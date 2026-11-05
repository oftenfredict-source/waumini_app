<?php

namespace App\Services\Church;

use App\Enums\LeadershipPosition;
use App\Enums\MemberRequestStatus;
use App\Enums\MemberStatus;
use App\Models\MemberRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberRequestCertificateService
{
    public function isEligible(MemberRequest $request): bool
    {
        if (! $request->type->generatesCertificate()) {
            return false;
        }

        return in_array($request->status, [
            MemberRequestStatus::Approved,
            MemberRequestStatus::Completed,
        ], true);
    }

    public function generate(MemberRequest $request, bool $clearRelease = true): MemberRequest
    {
        abort_unless($this->isEligible($request), 422, 'This request is not eligible for a certificate.');

        $output = $this->renderPdfOutput($request);
        $path = $this->storagePath($request);
        $releasedAt = $request->certificate_released_at;
        $releasedBy = $request->certificate_released_by;

        if ($request->certificate_path && $request->certificate_path !== $path) {
            Storage::disk('local')->delete($request->certificate_path);
        }

        Storage::disk('local')->put($path, $output);

        $payload = [
            'certificate_path' => $path,
            'certificate_generated_at' => now(),
        ];

        if ($clearRelease) {
            $payload['certificate_released_at'] = null;
            $payload['certificate_released_by'] = null;
        } else {
            $payload['certificate_released_at'] = $releasedAt;
            $payload['certificate_released_by'] = $releasedBy;
        }

        $request->update($payload);

        return $request->fresh();
    }

    public function ensureGenerated(MemberRequest $request): MemberRequest
    {
        if (! $this->isEligible($request)) {
            return $request;
        }

        if (
            $request->certificate_path
            && Storage::disk('local')->exists($request->certificate_path)
            && ! $this->officialToolsUpdatedSinceGeneration($request)
        ) {
            return $request;
        }

        return $this->generate($request);
    }

    public function streamPreview(MemberRequest $request): Response|StreamedResponse
    {
        abort_unless($request->type->generatesCertificate(), 422, 'This request type has no certificate.');

        if ($this->isEligible($request)) {
            // Always rebuild on staff preview so layout/template/seal changes show immediately.
            $request = $this->generate($request, clearRelease: false);

            abort_unless(
                $request->certificate_path && Storage::disk('local')->exists($request->certificate_path),
                404,
                'Certificate preview is not available yet.'
            );

            return response()->file(
                Storage::disk('local')->path($request->certificate_path),
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$this->downloadFilename($request).'"',
                ]
            );
        }

        return response($this->renderPdfOutput($request), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview-'.$this->downloadFilename($request).'"',
        ]);
    }

    public function release(MemberRequest $request, User $user): MemberRequest
    {
        abort_unless($this->isEligible($request), 422, 'Approve or complete the request before releasing the certificate.');

        $request = $this->ensureGenerated($request);

        abort_unless(
            $request->certificate_path && Storage::disk('local')->exists($request->certificate_path),
            404,
            'Certificate is not available yet.'
        );

        if (! $request->isCertificateReleased()) {
            $request->update([
                'certificate_released_at' => now(),
                'certificate_released_by' => $user->id,
            ]);
        }

        return $request->fresh(['certificateReleaser']);
    }

    public function download(MemberRequest $request): StreamedResponse
    {
        $request = $this->ensureGenerated($request);

        abort_unless(
            $request->certificate_path && Storage::disk('local')->exists($request->certificate_path),
            404,
            'Certificate is not available yet.'
        );

        return Storage::disk('local')->download(
            $request->certificate_path,
            $this->downloadFilename($request),
            ['Content-Type' => 'application/pdf'],
        );
    }

    private function renderPdfOutput(MemberRequest $request): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException(
                'Certificate PDF generation requires the PHP GD extension. Enable extension=gd in php.ini and restart the web server.'
            );
        }

        $request->loadMissing(['member.branch', 'church', 'assignedLeader.member', 'responder', 'branch']);

        $issuedAt = $request->responded_at ?? now();
        $branch = $request->member?->branch;

        $data = [
            'memberRequest' => $request,
            'church' => $request->church,
            'branch' => $branch,
            'displayName' => $branch?->name ?? $request->church->name,
            'displayAddress' => $branch?->address ?? $request->church->address,
            'displayCity' => $branch?->city ?? $request->church->city,
            'displayPhone' => $branch?->phone ?? $request->church->phone,
            'displayEmail' => $branch?->email ?? $request->church->email,
            'member' => $request->member,
            'leader' => $request->assignedLeader,
            'signatoryName' => $this->signatoryName($request),
            'signatoryTitle' => $this->signatoryTitleSwahili($request),
            'membershipStatus' => $this->membershipStatusSwahili($request->member?->status),
            'issuedAt' => $issuedAt,
            'issuedAtFormatted' => $this->formatSwahiliDate($issuedAt),
            'membershipDateFormatted' => $request->member?->membership_date
                ? $this->formatSwahiliDate($request->member->membership_date)
                : null,
            'churchLogoBase64' => $this->churchLogoBase64($branch, $request->church),
            'churchSealBase64' => $this->imageToBase64($request->church?->sealAbsolutePath()),
            'pastorSignatureBase64' => $this->imageToBase64($request->church?->pastorSignatureAbsolutePath()),
            'templateBackgroundBase64' => $this->imageToBase64(
                $request->church?->certificateTemplateAbsolutePath($request->type->value)
            ),
            'brandColor' => $request->church?->brandColor() ?? '#940000',
            'hasTemplateBackground' => (bool) $request->church?->hasCertificateTemplate($request->type->value),
        ];

        // Always generate a structured certificate. Uploaded template is only a decorative
        // background/frame — member details are laid out by the system, not scattered overlays.
        $view = $request->church?->hasCertificateTemplate($request->type->value)
            ? 'church.certificates.templated'
            : $request->type->certificateView();

        return Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->output();
    }

    public function downloadFilename(MemberRequest $request): string
    {
        $slug = str($request->member?->full_name ?? 'member')->slug('-');
        $date = ($request->responded_at ?? now())->format('Y-m-d');

        return "{$request->type->certificateFilenamePrefix()}-{$slug}-{$date}.pdf";
    }

    private function storagePath(MemberRequest $request): string
    {
        return "churches/{$request->church_id}/member-request-certificates/{$request->uuid}.pdf";
    }

    private function signatoryName(MemberRequest $request): string
    {
        if ($request->assignedLeader?->member?->full_name) {
            return $request->assignedLeader->member->full_name;
        }

        if ($request->responder?->name) {
            return $request->responder->name;
        }

        return $request->church?->pastor_name ?? 'Mchungaji wa Kanisa';
    }

    private function signatoryTitleSwahili(MemberRequest $request): string
    {
        $leader = $request->assignedLeader;

        if (! $leader) {
            return 'Kiongozi wa Kanisa';
        }

        if ($leader->position === LeadershipPosition::Other && $leader->position_title) {
            return $leader->position_title;
        }

        return match ($leader->position) {
            LeadershipPosition::Pastor => 'Mchungaji',
            LeadershipPosition::AssistantPastor => 'Msaidizi wa Mchungaji',
            LeadershipPosition::Secretary => 'Katibu',
            LeadershipPosition::AssistantSecretary => 'Msaidizi wa Katibu',
            LeadershipPosition::Treasurer => 'Mhazini',
            LeadershipPosition::AssistantTreasurer => 'Msaidizi wa Mhazini',
            LeadershipPosition::Accountant => 'Mhasibu',
            LeadershipPosition::Elder => 'Mzee wa Kanisa',
            LeadershipPosition::Deacon => 'Shamashi',
            LeadershipPosition::Deaconess => 'Shamasha',
            LeadershipPosition::YouthLeader => 'Kiongozi wa Vijana',
            LeadershipPosition::ChildrenLeader => 'Kiongozi wa Watoto',
            LeadershipPosition::WorshipLeader => 'Kiongozi wa Ibada',
            LeadershipPosition::ChoirLeader => 'Kiongozi wa Kwaya',
            LeadershipPosition::UsherLeader => 'Kiongozi wa Wakaribishaji',
            LeadershipPosition::EvangelismLeader => 'Kiongozi wa Uinjilisti',
            LeadershipPosition::PrayerLeader => 'Kiongozi wa Maombi',
            LeadershipPosition::Other => 'Kiongozi wa Kanisa',
        };
    }

    private function membershipStatusSwahili(?MemberStatus $status): string
    {
        return match ($status) {
            MemberStatus::Active => 'Hai',
            MemberStatus::Inactive => 'Haitumiki',
            default => '—',
        };
    }

    private function formatSwahiliDate(Carbon $date): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Machi', 4 => 'Aprili',
            5 => 'Mei', 6 => 'Juni', 7 => 'Julai', 8 => 'Agosti',
            9 => 'Septemba', 10 => 'Oktoba', 11 => 'Novemba', 12 => 'Desemba',
        ];

        return $date->format('j').' '.$months[(int) $date->format('n')].' '.$date->format('Y');
    }

    private function churchLogoBase64(?\App\Models\ChurchBranch $branch, ?\App\Models\Church $church): ?string
    {
        $path = $branch?->logoAbsolutePath() ?? $church?->logoAbsolutePath();

        return $this->imageToBase64($path);
    }

    private function imageToBase64(?string $path): ?string
    {
        if (! $path || ! extension_loaded('gd') || ! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }

    private function officialToolsUpdatedSinceGeneration(MemberRequest $request): bool
    {
        $request->loadMissing('church');

        $updatedAt = $request->church?->settings['official_tools_updated_at'] ?? null;

        if (! $updatedAt || ! $request->certificate_generated_at) {
            return false;
        }

        try {
            return Carbon::parse($updatedAt)->greaterThan($request->certificate_generated_at);
        } catch (\Throwable) {
            return false;
        }
    }
}

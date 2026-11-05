<?php

namespace App\Services\Owner;

use App\Models\DemoRequest;
use App\Models\SubscriptionPackage;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Sms\ChurchSmsService;
use App\Services\Sms\SmsGatewayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class DemoRequestService
{
    public function __construct(
        private readonly ChurchService $churchService,
        private readonly AuditLogService $auditLogService,
        private readonly SmsGatewayService $smsGateway,
        private readonly ChurchSmsService $churchSmsService,
    ) {}

    public function submit(array $data): DemoRequest
    {
        $request = DemoRequest::create([
            'church_name' => $data['church_name'],
            'contact_name' => $data['contact_name'],
            'email' => $data['email'],
            'phone' => $this->smsGateway->normalizePhone($data['phone'] ?? null) ?: ($data['phone'] ?? null),
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'package_id' => $data['package_id'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => DemoRequest::STATUS_PENDING,
        ]);

        $this->notifyOwnerOfNewRequest($request);

        return $request;
    }

    /**
     * @return array{request: DemoRequest, admin_password: string}
     */
    public function approve(DemoRequest $demoRequest, User $reviewer, ?int $packageId = null): array
    {
        if (! $demoRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only pending demo requests can be approved.',
            ]);
        }

        return DB::transaction(function () use ($demoRequest, $reviewer, $packageId) {
            if (User::where('email', $demoRequest->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'A user with this email already exists. Update the request email or create the church manually.',
                ]);
            }

            $package = $this->resolvePackage($demoRequest, $packageId);

            $result = $this->churchService->create([
                'name' => $demoRequest->church_name,
                'slug' => Str::slug($demoRequest->church_name),
                'email' => $demoRequest->email,
                'phone' => $demoRequest->phone,
                'city' => $demoRequest->city,
                'country' => $demoRequest->country,
                'pastor_name' => $demoRequest->contact_name,
                'admin_email' => $demoRequest->email,
                'branches_enabled' => $package?->hasFeature('branches') ?? false,
                'billing_cycle' => 'yearly',
            ], $package);

            $church = $result['church'];

            $demoRequest->update([
                'status' => DemoRequest::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'church_id' => $church->id,
                'package_id' => $package?->id ?? $demoRequest->package_id,
                'rejection_reason' => null,
            ]);

            $this->auditLogService->log(
                'demo_request.approved',
                $demoRequest,
                null,
                ['church_id' => $church->id, 'package_id' => $package?->id],
                $church->id
            );

            $this->notifyRequesterApproved($demoRequest->fresh(['church.primaryDomain']), $result['admin_password']);

            return [
                'request' => $demoRequest->fresh(['church', 'package', 'reviewer']),
                'admin_password' => $result['admin_password'],
            ];
        });
    }

    public function reject(DemoRequest $demoRequest, User $reviewer, ?string $reason = null): DemoRequest
    {
        if (! $demoRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only pending demo requests can be rejected.',
            ]);
        }

        $demoRequest->update([
            'status' => DemoRequest::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->auditLogService->log(
            'demo_request.rejected',
            $demoRequest,
            null,
            ['reason' => $reason],
            null
        );

        $this->notifyRequesterRejected($demoRequest->fresh());

        return $demoRequest;
    }

    private function resolvePackage(DemoRequest $demoRequest, ?int $packageId): ?SubscriptionPackage
    {
        $id = $packageId ?: $demoRequest->package_id;

        if ($id) {
            return SubscriptionPackage::query()->where('is_active', true)->find($id);
        }

        return SubscriptionPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();
    }

    private function notifyOwnerOfNewRequest(DemoRequest $request): void
    {
        $email = trim((string) SystemSetting::getValue('general', 'support_email', config('mail.from.address')));

        if ($email === '') {
            return;
        }

        $body = implode("\n", [
            'A new demo request was submitted on the landing page.',
            '',
            'Church: '.$request->church_name,
            'Contact: '.$request->contact_name,
            'Email: '.$request->email,
            'Phone: '.($request->phone ?: '—'),
            'City: '.($request->city ?: '—'),
            'Country: '.($request->country ?: '—'),
            'Message: '.($request->message ?: '—'),
            '',
            'Review it in the owner panel under Demo Requests.',
        ]);

        try {
            Mail::raw($body, function ($message) use ($email, $request): void {
                $message->to($email)
                    ->subject('New demo request: '.$request->church_name);
            });
        } catch (\Throwable) {
            // Keep the public submission successful even if mail fails.
        }
    }

    private function notifyRequesterApproved(DemoRequest $request, string $password): void
    {
        $church = $request->church;
        $loginUrl = $church
            ? $church->portalUrl('/login')
            : route('church.login');
        $trialDays = (int) ($request->package?->trial_days
            ?: $church?->activeSubscription?->package?->trial_days
            ?: 14);

        $emailBody = implode("\n", [
            'Hello '.$request->contact_name.',',
            '',
            'Your demo request for '.$request->church_name.' has been approved.',
            'Your '.$trialDays.'-day trial is ready.',
            '',
            'Login URL: '.$loginUrl,
            'Email: '.$request->email,
            'Temporary password: '.$password,
            '',
            'Please sign in and change your password after your first login.',
            '',
            '— '.config('app.name'),
        ]);

        $smsBody = implode(' ', [
            config('app.name').':',
            'Demo approved for '.$request->church_name.'.',
            $trialDays.'-day trial ready.',
            'Login: '.$loginUrl,
            'Email: '.$request->email,
            'Password: '.$password,
        ]);

        try {
            Mail::raw($emailBody, function ($message) use ($request): void {
                $message->to($request->email)
                    ->subject('Your Waumini Link demo trial is ready');
            });
        } catch (\Throwable) {
            // Credentials are also shown to the owner after approval.
        }

        $this->sendRequesterSms($request->phone, $smsBody, 'demo_approved');
    }

    private function notifyRequesterRejected(DemoRequest $request): void
    {
        $emailBody = implode("\n", array_filter([
            'Hello '.$request->contact_name.',',
            '',
            'Thank you for your interest in '.config('app.name').'.',
            'Unfortunately we are unable to approve the demo request for '.$request->church_name.' at this time.',
            $request->rejection_reason ? 'Reason: '.$request->rejection_reason : null,
            '',
            'You may contact us if you have questions.',
            '',
            '— '.config('app.name'),
        ]));

        $smsBody = implode(' ', array_filter([
            config('app.name').':',
            'Demo request for '.$request->church_name.' was not approved.',
            $request->rejection_reason ? 'Reason: '.$request->rejection_reason : null,
            'Contact support for help.',
        ]));

        try {
            Mail::raw($emailBody, function ($message) use ($request): void {
                $message->to($request->email)
                    ->subject('Update on your demo request');
            });
        } catch (\Throwable) {
            // Ignore mail failures on rejection.
        }

        $this->sendRequesterSms($request->phone, $smsBody, 'demo_rejected');
    }

    private function sendRequesterSms(?string $phone, string $message, string $context): void
    {
        $normalized = $this->smsGateway->normalizePhone($phone);

        if ($normalized === '') {
            return;
        }

        if (! $this->churchSmsService->platformSmsEnabled()) {
            Log::info('Demo request SMS skipped: platform SMS not enabled', ['context' => $context]);

            return;
        }

        try {
            $result = $this->smsGateway->send($normalized, $message);

            if (! ($result['ok'] ?? false)) {
                Log::warning('Demo request SMS was not delivered', [
                    'context' => $context,
                    'to' => $normalized,
                    'reason' => $result['reason'] ?? $result['body'] ?? 'unknown',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Demo request SMS failed: '.$e->getMessage(), ['context' => $context]);
        }
    }
}

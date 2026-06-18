<?php

namespace App\Services\Church;

use App\Enums\ChurchServiceStatus;
use App\Enums\PromiseGuestStatus;
use App\Models\Church;
use App\Models\ChurchService;
use App\Models\PromiseGuest;
use App\Models\SpecialEvent;
use App\Models\User;
use App\Services\Sms\ChurchSmsService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PromiseGuestService
{
    public function __construct(
        private readonly ChurchSmsService $churchSmsService,
    ) {}

    public function create(Church $church, array $data, ?User $creator = null): PromiseGuest
    {
        $data = $this->normalizeLinks($church, $data);
        $data['church_id'] = $church->id;
        $data['created_by'] = $creator?->id;
        $data['status'] = PromiseGuestStatus::Pending;
        $data['phone_number'] = $this->normalizePhone($data['phone_number'] ?? null);

        $guest = PromiseGuest::create($data);

        if (! empty($data['send_sms'])) {
            $this->sendNotification($guest->fresh(['church', 'churchService', 'specialEvent']));
        }

        return $guest->fresh(['churchService', 'specialEvent', 'creator']);
    }

    public function update(PromiseGuest $guest, array $data): PromiseGuest
    {
        $data = $this->normalizeLinks($guest->church, $data);

        if (array_key_exists('phone_number', $data)) {
            $data['phone_number'] = $this->normalizePhone($data['phone_number']);
        }

        $guest->update($data);

        return $guest->fresh(['churchService', 'specialEvent', 'creator']);
    }

    public function delete(PromiseGuest $guest): void
    {
        $guest->delete();
    }

    public function markAttended(PromiseGuest $guest): PromiseGuest
    {
        $guest->update(['status' => PromiseGuestStatus::Attended]);

        return $guest->fresh();
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function sendNotification(PromiseGuest $guest): array
    {
        $guest->loadMissing(['church', 'churchService', 'specialEvent']);

        if (empty($guest->phone_number)) {
            return ['ok' => false, 'message' => 'Phone number is required to send SMS.'];
        }

        $welcomeBack = $guest->status === PromiseGuestStatus::Attended;
        $hasUpcoming = false;

        if ($welcomeBack) {
            $upcoming = $this->findNextUpcomingVisit($guest);

            if ($upcoming !== null) {
                $service = $upcoming['churchService'];
                $event = $upcoming['specialEvent'];
                $promisedDate = $upcoming['promisedDate'];
                $hasUpcoming = true;
            } else {
                $service = null;
                $event = null;
                $promisedDate = null;
            }
        } else {
            $service = $guest->churchService;
            $event = $guest->specialEvent;
            $promisedDate = $guest->promised_date;
        }

        $result = $this->churchSmsService->sendPromiseGuestInvitation(
            $guest->church,
            $guest->phone_number,
            $guest->name,
            $promisedDate,
            $service,
            $event,
            $welcomeBack,
        );

        if ($result['ok'] ?? false) {
            $update = [
                'notified_at' => now(),
                'status' => PromiseGuestStatus::Notified,
            ];

            if ($welcomeBack && $hasUpcoming) {
                $update['promised_date'] = $promisedDate;
                $update['church_service_id'] = $service?->id;
                $update['special_event_id'] = $event?->id;
            }

            $guest->update($update);

            return [
                'ok' => true,
                'message' => $welcomeBack
                    ? ($hasUpcoming
                        ? 'Welcome back SMS sent to '.$guest->phone_number.' for the upcoming service.'
                        : 'Welcome back SMS sent to '.$guest->phone_number.'.')
                    : 'SMS sent successfully to '.$guest->phone_number.'.',
            ];
        }

        $reason = match ($result['reason'] ?? 'unknown') {
            'not_allowed' => 'SMS is not enabled for this church or package.',
            'disabled' => 'Church SMS notifications are disabled in Settings.',
            'limit_reached' => 'Monthly SMS limit reached.',
            default => 'SMS could not be sent. Check SMS gateway settings.',
        };

        Log::warning('Promise guest SMS failed', [
            'guest_id' => $guest->id,
            'reason' => $result['reason'] ?? 'unknown',
        ]);

        return ['ok' => false, 'message' => $reason];
    }

    /**
     * @return array{churchService: ?ChurchService, specialEvent: ?SpecialEvent, promisedDate: CarbonInterface}|null
     */
    private function findNextUpcomingVisit(PromiseGuest $guest): ?array
    {
        $churchId = $guest->church_id;
        $afterDate = $guest->promised_date->toDateString();

        if ($guest->churchService) {
            $service = ChurchService::query()
                ->forChurch($churchId)
                ->where('service_type', $guest->churchService->service_type)
                ->where('status', '!=', ChurchServiceStatus::Cancelled->value)
                ->whereDate('service_date', '>', $afterDate)
                ->orderBy('service_date')
                ->orderBy('start_time')
                ->first();

            if ($service) {
                return $this->upcomingVisitPayload($service, null);
            }
        }

        if ($guest->specialEvent) {
            $event = SpecialEvent::forChurch($churchId)
                ->whereDate('event_date', '>', $afterDate)
                ->orderBy('event_date')
                ->first();

            if ($event) {
                return $this->upcomingVisitPayload(null, $event);
            }
        }

        $service = ChurchService::query()
            ->forChurch($churchId)
            ->where('status', '!=', ChurchServiceStatus::Cancelled->value)
            ->whereDate('service_date', '>', $afterDate)
            ->orderBy('service_date')
            ->orderBy('start_time')
            ->first();

        if ($service) {
            return $this->upcomingVisitPayload($service, null);
        }

        $event = SpecialEvent::forChurch($churchId)
            ->whereDate('event_date', '>', $afterDate)
            ->orderBy('event_date')
            ->first();

        if ($event) {
            return $this->upcomingVisitPayload(null, $event);
        }

        return null;
    }

    /**
     * @return array{churchService: ?ChurchService, specialEvent: ?SpecialEvent, promisedDate: CarbonInterface}
     */
    private function upcomingVisitPayload(?ChurchService $service, ?SpecialEvent $event): array
    {
        return [
            'churchService' => $service,
            'specialEvent' => $event,
            'promisedDate' => $service?->service_date ?? $event?->event_date,
        ];
    }

    private function normalizeLinks(Church $church, array $data): array
    {
        $linkType = $data['event_link_type'] ?? null;
        unset($data['event_link_type'], $data['send_sms']);

        if ($linkType === 'church_service' && ! empty($data['church_service_id'])) {
            $service = ChurchService::forChurch($church->id)->findOrFail($data['church_service_id']);
            $data['church_service_id'] = $service->id;
            $data['special_event_id'] = null;
            $data['promised_date'] = $data['promised_date'] ?? $service->service_date?->toDateString();
        } elseif ($linkType === 'special_event' && ! empty($data['special_event_id'])) {
            $event = SpecialEvent::forChurch($church->id)->findOrFail($data['special_event_id']);
            $data['special_event_id'] = $event->id;
            $data['church_service_id'] = null;
            $data['promised_date'] = $data['promised_date'] ?? $event->event_date?->toDateString();
        } else {
            $data['church_service_id'] = null;
            $data['special_event_id'] = null;
        }

        if (! empty($data['church_service_id']) && ! empty($data['special_event_id'])) {
            throw ValidationException::withMessages([
                'church_service_id' => 'Select either a service or a special event, not both.',
            ]);
        }

        return $data;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $value = preg_replace('/\s+/', '', trim($phone));

        if (str_starts_with($value, '+255')) {
            return $value;
        }

        if (str_starts_with($value, '255')) {
            return '+'.$value;
        }

        if (str_starts_with($value, '0')) {
            $value = substr($value, 1);
        }

        return '+255'.$value;
    }
}

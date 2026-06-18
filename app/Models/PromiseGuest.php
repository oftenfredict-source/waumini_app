<?php

namespace App\Models;

use App\Enums\PromiseGuestStatus;
use App\Enums\PromiseGuestType;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromiseGuest extends Model
{
    use BelongsToChurch, HasUuid;

    protected $fillable = [
        'church_id',
        'guest_type',
        'name',
        'phone_number',
        'email',
        'promised_date',
        'church_service_id',
        'special_event_id',
        'status',
        'notified_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'guest_type' => PromiseGuestType::class,
            'promised_date' => 'date',
            'status' => PromiseGuestStatus::class,
            'notified_at' => 'datetime',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function churchService(): BelongsTo
    {
        return $this->belongsTo(ChurchService::class);
    }

    public function specialEvent(): BelongsTo
    {
        return $this->belongsTo(SpecialEvent::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function eventLabel(): string
    {
        if ($this->churchService) {
            return $this->churchService->offeringSelectionLabel();
        }

        if ($this->specialEvent) {
            $date = $this->specialEvent->event_date?->format('M d, Y') ?? '—';

            return $this->specialEvent->title.' — '.$date;
        }

        return $this->promised_date?->format('M d, Y') ?? '—';
    }
}

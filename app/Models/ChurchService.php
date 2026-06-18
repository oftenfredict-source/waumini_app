<?php

namespace App\Models;

use App\Enums\AttendanceSourceType;
use App\Enums\ChurchServiceStatus;
use App\Enums\ChurchServiceType;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChurchService extends Model
{
    use BelongsToChurch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'service_type',
        'title',
        'service_date',
        'start_time',
        'end_time',
        'theme',
        'preacher',
        'venue',
        'status',
        'notes',
        'guests_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'service_type' => ChurchServiceType::class,
            'status' => ChurchServiceStatus::class,
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'source_id')
            ->where('source_type', AttendanceSourceType::ChurchService->value);
    }

    public function displayTitle(): string
    {
        if ($this->service_type === ChurchServiceType::Extra && $this->title) {
            return $this->title;
        }

        return $this->service_type->label();
    }

    public function isSundaySchool(): bool
    {
        return $this->service_type === ChurchServiceType::SundaySchool;
    }

    public function scopeForOfferingSelection(Builder $query, int $churchId): Builder
    {
        return $query->forChurch($churchId)
            ->whereIn('service_type', [
                ChurchServiceType::Sunday->value,
                ChurchServiceType::MidWeek->value,
                ChurchServiceType::SundaySchool->value,
                ChurchServiceType::Prayer->value,
                ChurchServiceType::Extra->value,
            ])
            ->where('status', '!=', ChurchServiceStatus::Cancelled->value)
            ->whereDate('service_date', '>=', now()->subYear()->toDateString())
            ->orderByDesc('service_date')
            ->orderByDesc('start_time');
    }

    public function offeringSelectionLabel(): string
    {
        $date = $this->service_date?->format('M d, Y') ?? '—';

        return $this->displayTitle().' — '.$date;
    }
}

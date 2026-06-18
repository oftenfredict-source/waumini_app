<?php

namespace App\Models;

use App\Enums\CelebrationSource;
use App\Enums\CelebrationStatus;
use App\Enums\CelebrationType;
use App\Enums\WeddingType;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Celebration extends Model
{
    use BelongsToChurch, HasUuid;

    protected $fillable = [
        'church_id',
        'member_id',
        'celebration_type',
        'source',
        'title',
        'celebration_date',
        'original_date',
        'wedding_type',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'celebration_type' => CelebrationType::class,
            'source' => CelebrationSource::class,
            'status' => CelebrationStatus::class,
            'wedding_type' => WeddingType::class,
            'celebration_date' => 'date',
            'original_date' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->where('status', CelebrationStatus::Upcoming)
            ->whereDate('celebration_date', '>=', now()->toDateString())
            ->orderBy('celebration_date');
    }

    public function scopeWithinDays(Builder $query, int $days): Builder
    {
        return $query
            ->whereDate('celebration_date', '>=', now()->toDateString())
            ->whereDate('celebration_date', '<=', now()->addDays($days)->toDateString());
    }

    public function scopeInCalendarMonth(Builder $query, int $month, int $year): Builder
    {
        return $query
            ->whereYear('celebration_date', $year)
            ->whereMonth('celebration_date', $month);
    }

    public function scopeForCelebrationYear(Builder $query, int $year): Builder
    {
        return $query->whereYear('celebration_date', $year);
    }

    public function scopeNotPassedYet(Builder $query, int $year): Builder
    {
        if ($year === (int) now()->year) {
            return $query->whereDate('celebration_date', '>=', now()->toDateString());
        }

        return $query;
    }

    public function scopeInEventMonth(Builder $query, int $month): Builder
    {
        return $query->where(function (Builder $q) use ($month) {
            $q->where(function (Builder $sub) use ($month) {
                $sub->whereNotNull('original_date')
                    ->whereMonth('original_date', $month);
            })->orWhere(function (Builder $sub) use ($month) {
                $sub->whereNull('original_date')
                    ->whereMonth('celebration_date', $month);
            });
        });
    }

    public function daysUntil(): ?int
    {
        $today = now()->startOfDay();

        if ($this->celebration_date->lt($today)) {
            return null;
        }

        return (int) $today->diffInDays($this->celebration_date->startOfDay(), false);
    }

    public function isWithinDays(int $days): bool
    {
        $until = $this->daysUntil();

        return $until !== null && $until <= $days;
    }

    public function displayLabel(): string
    {
        if ($this->member) {
            return $this->member->full_name;
        }

        return $this->title;
    }

    public function yearsCount(): ?int
    {
        if (! $this->original_date) {
            return null;
        }

        return max(1, $this->celebration_date->year - $this->original_date->year);
    }

    public function isEditable(): bool
    {
        return $this->source === CelebrationSource::Manual;
    }
}

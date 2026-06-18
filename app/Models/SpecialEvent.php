<?php

namespace App\Models;

use App\Enums\AttendanceSourceType;
use App\Enums\SpecialEventCategory;
use App\Enums\SpecialEventStatus;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialEvent extends Model
{
    use BelongsToChurch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'title',
        'category',
        'category_other',
        'event_date',
        'start_time',
        'end_time',
        'speaker',
        'venue',
        'budget_amount',
        'expected_attendance',
        'status',
        'description',
        'notes',
        'guests_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'category' => SpecialEventCategory::class,
            'status' => SpecialEventStatus::class,
            'budget_amount' => 'decimal:2',
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
            ->where('source_type', AttendanceSourceType::SpecialEvent->value);
    }

    public function categoryLabel(): string
    {
        if ($this->category === SpecialEventCategory::Other && $this->category_other) {
            return $this->category_other;
        }

        return $this->category->label();
    }
}

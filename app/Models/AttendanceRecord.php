<?php

namespace App\Models;

use App\Enums\AttendanceSourceType;
use App\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'source_type',
        'source_id',
        'member_id',
        'dependant_id',
        'attended_at',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'source_type' => AttendanceSourceType::class,
            'attended_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function dependant(): BelongsTo
    {
        return $this->belongsTo(MemberDependant::class, 'dependant_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeForSource(Builder $query, AttendanceSourceType|string $type, int $sourceId): Builder
    {
        $value = $type instanceof AttendanceSourceType ? $type->value : $type;

        return $query->where('source_type', $value)->where('source_id', $sourceId);
    }

    public function scopeMembersOnly(Builder $query): Builder
    {
        return $query->whereNotNull('member_id');
    }

    public function scopeChildrenOnly(Builder $query): Builder
    {
        return $query->whereNotNull('dependant_id');
    }

    public function attendeeName(): string
    {
        if ($this->member_id) {
            return $this->member?->full_name ?? 'Unknown member';
        }

        return $this->dependant?->full_name ?? 'Unknown child';
    }
}

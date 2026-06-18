<?php

namespace App\Models;

use App\Enums\AnnouncementTargetType;
use App\Enums\AnnouncementType;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use BelongsToChurch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'title',
        'content',
        'type',
        'target_type',
        'department_id',
        'start_date',
        'end_date',
        'is_active',
        'is_pinned',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => AnnouncementType::class,
            'target_type' => AnnouncementTargetType::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'is_pinned' => 'boolean',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function targetedMembers(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'announcement_member')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            });
    }

    public function scopeTargetedForMember(Builder $query, Member $member): Builder
    {
        $departmentIds = $member->departments()->pluck('departments.id');

        return $query->where(function ($q) use ($member, $departmentIds) {
            $q->where('target_type', AnnouncementTargetType::All)
                ->orWhere(function ($q) use ($member) {
                    $q->where('target_type', AnnouncementTargetType::Specific)
                        ->whereHas('targetedMembers', fn ($relation) => $relation->where('members.id', $member->id));
                })
                ->orWhere(function ($q) use ($departmentIds) {
                    $q->where('target_type', AnnouncementTargetType::Department);
                    if ($departmentIds->isNotEmpty()) {
                        $q->whereIn('department_id', $departmentIds);
                    } else {
                        $q->whereRaw('0 = 1');
                    }
                });
        });
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->start_date && $this->start_date->gt($today)) {
            return false;
        }

        if ($this->end_date && $this->end_date->lt($today)) {
            return false;
        }

        return true;
    }

    public function targetLabel(): string
    {
        return match ($this->target_type) {
            AnnouncementTargetType::All => 'All members',
            AnnouncementTargetType::Department => ($this->department?->name ?? 'Department').' ('.$this->targetedMembers()->count().' members)',
            AnnouncementTargetType::Specific => $this->targetedMembers()->count().' selected member(s)',
        };
    }
}

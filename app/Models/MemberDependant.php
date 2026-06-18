<?php

namespace App\Models;

use App\Enums\DependantRelationship;
use App\Models\Church;
use App\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDependant extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'member_id',
        'guardian_full_name',
        'guardian_phone',
        'guardian_relationship',
        'full_name',
        'gender',
        'date_of_birth',
        'is_baptized',
        'baptism_date',
        'baptism_place',
        'baptized_by',
        'relationship',
        'relationship_note',
        'linked_member_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_baptized' => 'boolean',
            'baptism_date' => 'date',
            'relationship' => DependantRelationship::class,
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function linkedMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'linked_member_id');
    }

    public function scopeChildren(Builder $query): Builder
    {
        return $query->where('relationship', DependantRelationship::Child);
    }

    public function scopeEligibleForIndependence(Builder $query): Builder
    {
        $age = config('membership.child_independence_age', 21);

        return $query->whereNull('linked_member_id')
            ->where('relationship', DependantRelationship::Child)
            ->whereNotNull('date_of_birth')
            ->whereDate('date_of_birth', '<=', now()->subYears($age)->toDateString());
    }

    public function scopeForSundaySchool(Builder $query): Builder
    {
        $minAge = config('membership.sunday_school_min_age', 3);
        $maxAge = config('membership.sunday_school_max_age', 12);

        return $query->children()
            ->whereNull('linked_member_id')
            ->whereNotNull('date_of_birth')
            ->whereDate('date_of_birth', '<=', now()->subYears($minAge)->toDateString())
            ->whereDate('date_of_birth', '>', now()->subYears($maxAge + 1)->toDateString());
    }

    public function scopeForMainServiceAttendance(Builder $query): Builder
    {
        $minAge = config('membership.main_service_child_min_age', 13);
        $maxAge = config('membership.child_independence_age', 21) - 1;

        return $query->children()
            ->whereNull('linked_member_id')
            ->whereNotNull('date_of_birth')
            ->whereDate('date_of_birth', '<=', now()->subYears($minAge)->toDateString())
            ->whereDate('date_of_birth', '>', now()->subYears($maxAge + 1)->toDateString());
    }

    public function shouldAttendSundaySchool(): bool
    {
        $age = $this->age();

        if ($age === null || $this->relationship !== DependantRelationship::Child || $this->isConverted()) {
            return false;
        }

        return $age >= config('membership.sunday_school_min_age', 3)
            && $age <= config('membership.sunday_school_max_age', 12);
    }

    public function shouldAttendMainService(): bool
    {
        $age = $this->age();

        if ($age === null || $this->relationship !== DependantRelationship::Child || $this->isConverted()) {
            return false;
        }

        $minAge = config('membership.main_service_child_min_age', 13);
        $maxAge = config('membership.child_independence_age', 21) - 1;

        return $age >= $minAge && $age <= $maxAge;
    }

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function hasMemberParent(): bool
    {
        return $this->member_id !== null;
    }

    public function guardianDisplayName(): string
    {
        if ($this->member) {
            return $this->member->full_name;
        }

        return $this->guardian_full_name ?? '—';
    }

    public function isConverted(): bool
    {
        return $this->linked_member_id !== null;
    }

    public function isEligibleForIndependence(): bool
    {
        if ($this->isConverted() || $this->relationship !== DependantRelationship::Child) {
            return false;
        }

        $age = $this->age();

        return $age !== null && $age >= config('membership.child_independence_age', 21);
    }

    public function independenceStatusLabel(): string
    {
        if ($this->isConverted()) {
            return 'Independent member';
        }

        if ($this->isEligibleForIndependence()) {
            return 'Ready to convert';
        }

        return 'Active child';
    }
}

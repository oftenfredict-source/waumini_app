<?php

namespace App\Models;

use App\Enums\EducationLevel;
use App\Enums\MaritalStatus;
use App\Enums\MemberStatus;
use App\Enums\MemberType;
use App\Enums\MembershipType;
use App\Enums\TemporaryDurationUnit;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Member extends Model
{
    use BelongsToChurch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'branch_id',
        'member_number',
        'envelope_number',
        'member_type',
        'membership_type',
        'temporary_duration_value',
        'temporary_duration_unit',
        'full_name',
        'email',
        'phone_number',
        'gender',
        'date_of_birth',
        'education_level',
        'profession',
        'nida_number',
        'is_baptized',
        'baptism_date',
        'baptism_place',
        'baptized_by',
        'profile_picture',
        'region',
        'district',
        'ward',
        'street',
        'po_box',
        'tribe',
        'other_tribe',
        'residence_region',
        'residence_district',
        'residence_ward',
        'residence_street',
        'residence_road',
        'residence_house_number',
        'address',
        'city',
        'marital_status',
        'wedding_type',
        'wedding_date',
        'spouse_full_name',
        'spouse_gender',
        'spouse_date_of_birth',
        'spouse_education_level',
        'spouse_profession',
        'spouse_nida_number',
        'spouse_email',
        'spouse_phone_number',
        'spouse_tribe',
        'spouse_other_tribe',
        'spouse_church_member',
        'spouse_member_id',
        'spouse_envelope_number',
        'membership_date',
        'membership_expires_at',
        'status',
        'archived_at',
        'archive_reason',
        'archived_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'spouse_date_of_birth' => 'date',
            'membership_date' => 'date',
            'membership_expires_at' => 'date',
            'membership_type' => MembershipType::class,
            'temporary_duration_unit' => TemporaryDurationUnit::class,
            'member_type' => MemberType::class,
            'education_level' => EducationLevel::class,
            'spouse_education_level' => EducationLevel::class,
            'marital_status' => MaritalStatus::class,
            'wedding_type' => \App\Enums\WeddingType::class,
            'wedding_date' => 'date',
            'status' => MemberStatus::class,
            'archived_at' => 'datetime',
            'is_baptized' => 'boolean',
            'baptism_date' => 'date',
        ];
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null || $this->status === MemberStatus::Inactive;
    }

    public function scopeActiveMembers(Builder $query): Builder
    {
        return $query
            ->where('status', MemberStatus::Active)
            ->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNotNull('archived_at')
                ->orWhere('status', MemberStatus::Inactive);
        });
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ChurchBranch::class, 'branch_id');
    }

    public function leaders(): HasMany
    {
        return $this->hasMany(Leader::class);
    }

    public function dependants(): HasMany
    {
        return $this->hasMany(MemberDependant::class);
    }

    public function spouseMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'spouse_member_id');
    }

    public function spouseOf(): HasOne
    {
        return $this->hasOne(Member::class, 'spouse_member_id');
    }

    public function resolvedSpouse(): ?Member
    {
        if ($this->spouseMember) {
            return $this->spouseMember;
        }

        if ($this->relationLoaded('spouseOf')) {
            return $this->spouseOf;
        }

        return $this->spouseOf()->first();
    }

    /**
     * @return array<int, int>
     */
    public function familyMemberIds(): array
    {
        $ids = [$this->id];
        $spouse = $this->resolvedSpouse();

        if ($spouse) {
            $ids[] = $spouse->id;
        }

        return array_values(array_unique($ids));
    }

    /**
     * Children and dependants registered on this member or their spouse.
     *
     * @return Collection<int, MemberDependant>
     */
    public function familyDependants(): Collection
    {
        return MemberDependant::forChurch($this->church_id)
            ->whereIn('member_id', $this->familyMemberIds())
            ->with(['linkedMember', 'member'])
            ->orderBy('full_name')
            ->get();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class);
    }

    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_member')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function profilePictureUrl(): ?string
    {
        return $this->profile_picture
            ? asset('storage/'.$this->profile_picture)
            : null;
    }

    public function isTemporary(): bool
    {
        return $this->membership_type === MembershipType::Temporary;
    }

    public function isMembershipExpired(): bool
    {
        return $this->membership_expires_at !== null
            && $this->membership_expires_at->isPast();
    }

    public function membershipDaysRemaining(): ?int
    {
        if (! $this->membership_expires_at || $this->isMembershipExpired()) {
            return $this->membership_expires_at ? 0 : null;
        }

        return (int) now()->startOfDay()->diffInDays($this->membership_expires_at->startOfDay());
    }

    public function temporaryDurationLabel(): ?string
    {
        if (! $this->temporary_duration_value || ! $this->temporary_duration_unit) {
            return null;
        }

        $unit = $this->temporary_duration_unit === TemporaryDurationUnit::Year ? 'year' : 'month';
        $plural = $this->temporary_duration_value > 1 ? 's' : '';

        return "{$this->temporary_duration_value} {$unit}{$plural}";
    }
}

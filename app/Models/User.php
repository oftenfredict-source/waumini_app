<?php

namespace App\Models;

use App\Enums\ChurchStaffRole;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Traits\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_type',
        'status',
        'church_id',
        'branch_id',
        'member_id',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
            'status' => UserStatus::class,
        ];
    }

    public function isOwnerUser(): bool
    {
        return in_array($this->user_type, [UserType::Owner, UserType::Staff], true)
            && $this->status === UserStatus::Active;
    }

    public function isChurchUser(): bool
    {
        return in_array($this->user_type, UserType::churchStaffTypes(), true)
            && $this->status === UserStatus::Active
            && $this->church_id !== null;
    }

    public function isChurchMember(): bool
    {
        return $this->user_type === UserType::Member
            && $this->status === UserStatus::Active
            && $this->church_id !== null
            && $this->member_id !== null;
    }

    public function hasLinkedMember(): bool
    {
        return $this->church_id !== null
            && $this->member_id !== null
            && $this->member !== null;
    }

    public function canAccessMemberPortal(): bool
    {
        return $this->isChurchPortalUser()
            && $this->hasLinkedMember();
    }

    public function isChurchPortalUser(): bool
    {
        return in_array($this->user_type, UserType::churchPortalTypes(), true)
            && $this->status === UserStatus::Active
            && $this->church_id !== null;
    }

    public function isChurchAdmin(): bool
    {
        return $this->user_type === UserType::ChurchAdmin
            && $this->status === UserStatus::Active;
    }

    public function isPastor(): bool
    {
        return $this->user_type === UserType::Pastor
            && $this->status === UserStatus::Active;
    }

    public function isSecretary(): bool
    {
        return $this->user_type === UserType::Secretary
            && $this->status === UserStatus::Active;
    }

    public function isTreasurer(): bool
    {
        return $this->user_type === UserType::Treasurer
            && $this->status === UserStatus::Active;
    }

    public function isAccountant(): bool
    {
        return $this->user_type === UserType::Accountant
            && $this->status === UserStatus::Active;
    }

    public function canManageMemberPasswords(): bool
    {
        return $this->isChurchAdmin()
            || $this->hasRole(ChurchStaffRole::Administrator->value);
    }

    public function churchRoleLabel(): string
    {
        return ChurchStaffRole::fromUserType($this->user_type)?->label()
            ?? ($this->isChurchMember() ? 'Member' : 'Church User');
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ChurchBranch::class, 'branch_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function loginPhone(): ?string
    {
        if (! empty($this->phone)) {
            return $this->phone;
        }

        if (! empty($this->member?->phone_number)) {
            return $this->member->phone_number;
        }

        if ($this->church_id && in_array($this->user_type, UserType::churchStaffTypes(), true)) {
            $churchPhone = $this->church?->phone;

            return ! empty($churchPhone) ? $churchPhone : null;
        }

        return null;
    }

    public static function findByLoginIdentifier(string $identifier): ?self
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        $user = static::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($identifier)])
            ->first();

        if ($user) {
            return $user;
        }

        return static::query()
            ->whereHas('member', function ($query) use ($identifier) {
                $query->where('member_number', $identifier)
                    ->orWhereRaw('LOWER(member_number) = ?', [strtolower($identifier)]);
            })
            ->first();
    }
}

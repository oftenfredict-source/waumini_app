<?php

namespace App\Models;

use App\Enums\MemberRegistrationStatus;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberRegistrationApplication extends Model
{
    use BelongsToChurch, HasUuid;

    protected $fillable = [
        'church_id',
        'branch_id',
        'application_number',
        'full_name',
        'phone_number',
        'registration_data',
        'dependants_data',
        'profile_picture_path',
        'status',
        'assigned_envelope_number',
        'member_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'registration_data' => 'array',
            'dependants_data' => 'array',
            'status' => MemberRegistrationStatus::class,
            'reviewed_at' => 'datetime',
        ];
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === MemberRegistrationStatus::Pending;
    }

    public function profilePictureUrl(): ?string
    {
        return $this->profile_picture_path
            ? asset('storage/'.$this->profile_picture_path)
            : null;
    }
}

<?php

namespace App\Models;

use App\Enums\MemberRequestStatus;
use App\Enums\MemberRequestType;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberRequest extends Model
{
    use BelongsToChurch, HasUuid;

    protected $fillable = [
        'church_id',
        'branch_id',
        'member_id',
        'assigned_leader_id',
        'reference_number',
        'type',
        'subject',
        'description',
        'request_meta',
        'status',
        'response',
        'responded_by',
        'responded_at',
        'certificate_path',
        'certificate_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => MemberRequestType::class,
            'status' => MemberRequestStatus::class,
            'request_meta' => 'array',
            'responded_at' => 'datetime',
            'certificate_generated_at' => 'datetime',
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

    public function assignedLeader(): BelongsTo
    {
        return $this->belongsTo(Leader::class, 'assigned_leader_id');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            MemberRequestStatus::Pending,
            MemberRequestStatus::InReview,
        ], true);
    }

    public function hasDownloadableCertificate(): bool
    {
        if (! $this->type->generatesCertificate()) {
            return false;
        }

        return in_array($this->status, [
            MemberRequestStatus::Approved,
            MemberRequestStatus::Completed,
        ], true);
    }
}

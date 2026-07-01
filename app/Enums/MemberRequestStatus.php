<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum MemberRequestStatus: string
{
    use HasTranslatableLabel;

    case Pending = 'pending';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::InReview => 'info',
            self::Approved => 'primary',
            self::Rejected => 'danger',
            self::Completed => 'success',
        };
    }
}

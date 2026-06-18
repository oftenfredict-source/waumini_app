<?php

namespace App\Enums;

enum MemberRequestStatus: string
{
    case Pending = 'pending';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InReview => 'In Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Completed => 'Completed',
        };
    }

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

<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum ChurchStaffRole: string
{
    use HasTranslatableLabel;

    case Administrator = 'administrator';
    case Pastor = 'pastor';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';
    case Accountant = 'accountant';

    public function userType(): UserType
    {
        return match ($this) {
            self::Administrator => UserType::ChurchAdmin,
            self::Pastor => UserType::Pastor,
            self::Secretary => UserType::Secretary,
            self::Treasurer => UserType::Treasurer,
            self::Accountant => UserType::Accountant,
        };
    }

    public static function fromUserType(UserType $type): ?self
    {
        return match ($type) {
            UserType::ChurchAdmin => self::Administrator,
            UserType::Pastor => self::Pastor,
            UserType::Secretary => self::Secretary,
            UserType::Treasurer => self::Treasurer,
            UserType::Accountant => self::Accountant,
            default => null,
        };
    }

    public static function fromLeadershipPosition(LeadershipPosition $position): ?self
    {
        return match ($position) {
            LeadershipPosition::Pastor, LeadershipPosition::AssistantPastor => self::Pastor,
            LeadershipPosition::Secretary, LeadershipPosition::AssistantSecretary => self::Secretary,
            LeadershipPosition::Treasurer, LeadershipPosition::AssistantTreasurer => self::Treasurer,
            LeadershipPosition::Accountant => self::Accountant,
            default => null,
        };
    }

    /** @return list<self> */
    public static function leadershipPriority(): array
    {
        return [
            self::Pastor,
            self::Secretary,
            self::Treasurer,
            self::Accountant,
        ];
    }
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum ChurchStaffRole: string
{
    use HasTranslatableLabel;

    case Administrator = 'administrator';
    case Pastor = 'pastor';
    case AssistantPastor = 'assistant_pastor';
    case Elder = 'elder';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';
    case Accountant = 'accountant';

    public function userType(): UserType
    {
        return match ($this) {
            self::Administrator => UserType::ChurchAdmin,
            self::Pastor => UserType::Pastor,
            self::AssistantPastor => UserType::AssistantPastor,
            self::Elder => UserType::Elder,
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
            UserType::AssistantPastor => self::AssistantPastor,
            UserType::Elder => self::Elder,
            UserType::Secretary => self::Secretary,
            UserType::Treasurer => self::Treasurer,
            UserType::Accountant => self::Accountant,
            default => null,
        };
    }

    public static function fromLeadershipPosition(LeadershipPosition $position): ?self
    {
        return match ($position) {
            LeadershipPosition::Pastor => self::Pastor,
            LeadershipPosition::AssistantPastor => self::AssistantPastor,
            LeadershipPosition::Elder => self::Elder,
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
            self::AssistantPastor,
            self::Secretary,
            self::Treasurer,
            self::Accountant,
            self::Elder,
        ];
    }

    /**
     * Roles whose permissions a church admin can customize in System → Roles.
     *
     * @return list<self>
     */
    public static function configurableCases(): array
    {
        return self::cases();
    }
}

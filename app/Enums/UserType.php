<?php

namespace App\Enums;

enum UserType: string
{
    case Owner = 'owner';
    case Staff = 'staff';
    case ChurchAdmin = 'church_admin';
    case Pastor = 'pastor';
    case AssistantPastor = 'assistant_pastor';
    case Elder = 'elder';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';
    case Accountant = 'accountant';
    case Member = 'member';

    public static function churchStaffTypes(): array
    {
        return [
            self::ChurchAdmin,
            self::Pastor,
            self::AssistantPastor,
            self::Elder,
            self::Secretary,
            self::Treasurer,
            self::Accountant,
        ];
    }

    public static function churchPortalTypes(): array
    {
        return array_merge(self::churchStaffTypes(), [self::Member]);
    }
}

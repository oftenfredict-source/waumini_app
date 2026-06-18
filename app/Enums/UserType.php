<?php

namespace App\Enums;

enum UserType: string
{
    case Owner = 'owner';
    case Staff = 'staff';
    case ChurchAdmin = 'church_admin';
    case Pastor = 'pastor';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';
    case Accountant = 'accountant';
    case Member = 'member';

    public static function churchStaffTypes(): array
    {
        return [
            self::ChurchAdmin,
            self::Pastor,
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

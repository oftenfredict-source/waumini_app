<?php

namespace App\Enums;

enum LeadershipPosition: string
{
    case Pastor = 'pastor';
    case AssistantPastor = 'assistant_pastor';
    case Secretary = 'secretary';
    case AssistantSecretary = 'assistant_secretary';
    case Treasurer = 'treasurer';
    case AssistantTreasurer = 'assistant_treasurer';
    case Accountant = 'accountant';
    case Elder = 'elder';
    case Deacon = 'deacon';
    case Deaconess = 'deaconess';
    case YouthLeader = 'youth_leader';
    case ChildrenLeader = 'children_leader';
    case WorshipLeader = 'worship_leader';
    case ChoirLeader = 'choir_leader';
    case UsherLeader = 'usher_leader';
    case EvangelismLeader = 'evangelism_leader';
    case PrayerLeader = 'prayer_leader';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Pastor => 'Pastor',
            self::AssistantPastor => 'Assistant Pastor',
            self::Secretary => 'Secretary',
            self::AssistantSecretary => 'Assistant Secretary',
            self::Treasurer => 'Treasurer',
            self::AssistantTreasurer => 'Assistant Treasurer',
            self::Accountant => 'Accountant',
            self::Elder => 'Church Elder',
            self::Deacon => 'Deacon',
            self::Deaconess => 'Deaconess',
            self::YouthLeader => 'Youth Leader',
            self::ChildrenLeader => 'Children Leader',
            self::WorshipLeader => 'Worship Leader',
            self::ChoirLeader => 'Choir Leader',
            self::UsherLeader => 'Usher Leader',
            self::EvangelismLeader => 'Evangelism Leader',
            self::PrayerLeader => 'Prayer Leader',
            self::Other => 'Other (Custom)',
        };
    }

    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}

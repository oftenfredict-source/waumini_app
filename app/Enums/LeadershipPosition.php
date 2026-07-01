<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum LeadershipPosition: string
{
    use HasTranslatableLabel;

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

    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}

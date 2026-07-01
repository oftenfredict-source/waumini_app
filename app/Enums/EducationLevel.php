<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum EducationLevel: string
{
    use HasTranslatableLabel;

    case NotStudied = 'not_studied';
    case Primary = 'primary';
    case Secondary = 'secondary';
    case HighLevel = 'high_level';
    case Certificate = 'certificate';
    case Diploma = 'diploma';
    case BachelorDegree = 'bachelor_degree';
    case Masters = 'masters';
    case Phd = 'phd';
    case Professor = 'professor';

}

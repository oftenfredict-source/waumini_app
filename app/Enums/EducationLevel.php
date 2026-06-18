<?php

namespace App\Enums;

enum EducationLevel: string
{
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

    public function label(): string
    {
        return match ($this) {
            self::NotStudied => 'Not Studied',
            self::Primary => 'Primary',
            self::Secondary => 'Secondary',
            self::HighLevel => 'Advanced Secondary',
            self::Certificate => 'Certificate',
            self::Diploma => 'Diploma',
            self::BachelorDegree => 'Bachelor Degree',
            self::Masters => 'Masters',
            self::Phd => 'PhD',
            self::Professor => 'Professor',
        };
    }
}

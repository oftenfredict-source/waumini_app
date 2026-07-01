<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum MemberRequestType: string
{
    use HasTranslatableLabel;

    case TravellingCertificate = 'travelling_certificate';
    case RecommendationLetter = 'recommendation_letter';
    case BaptismCertificate = 'baptism_certificate';
    case BaptismRequest = 'baptism_request';
    case GeneralIssue = 'general_issue';
    case PrayerRequest = 'prayer_request';
    case Other = 'other';

    public function generatesCertificate(): bool
    {
        return in_array($this, [
            self::TravellingCertificate,
            self::RecommendationLetter,
            self::BaptismCertificate,
        ], true);
    }

    public function certificateView(): string
    {
        return match ($this) {
            self::TravellingCertificate => 'church.certificates.travelling-certificate',
            self::RecommendationLetter => 'church.certificates.recommendation-letter',
            self::BaptismCertificate => 'church.certificates.baptism-certificate',
            default => 'church.certificates.travelling-certificate',
        };
    }

    public function certificateFilenamePrefix(): string
    {
        return match ($this) {
            self::TravellingCertificate => 'travelling-certificate',
            self::RecommendationLetter => 'recommendation-letter',
            self::BaptismCertificate => 'baptism-certificate',
            default => 'certificate',
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

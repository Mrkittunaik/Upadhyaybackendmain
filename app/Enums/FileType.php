<?php

namespace App\Enums;

enum FileType: string
{
    case ProfilePhoto = 'profile_photo';
    case Resume = 'resume';
    case Certificate = 'certificate';
    case CompanyLogo = 'company_logo';
    case JobImage = 'job_image';
    case Document = 'document';
    case Video = 'video';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

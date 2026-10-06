<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case Reviewing = 'reviewing';
    case Shortlisted = 'shortlisted';
    case Rejected = 'rejected';
    case Selected = 'selected';
    case Withdrawn = 'withdrawn';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

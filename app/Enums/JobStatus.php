<?php

namespace App\Enums;

enum JobStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
    case Expired = 'expired';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Enums;

enum BranchStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Faculty = 'faculty';
    case CompanyMain = 'company_main';
    case CompanyBranch = 'company_branch';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

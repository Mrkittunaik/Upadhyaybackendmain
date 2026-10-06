<?php

namespace App\Services;

use App\Models\CompanyBranch;
use RuntimeException;

/**
 * Generates the unique 5-digit numeric branch code. Server-side only.
 * The unique index on company_branches.branch_code is the final safeguard;
 * call inside the approval DB transaction.
 */
class BranchCodeGenerator
{
    public function generate(int $maxAttempts = 20): string
    {
        $length = (int) config('upadhyay.branch_code_length', 5);

        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

            if (! CompanyBranch::withTrashed()->where('branch_code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Could not generate a unique branch code.');
    }

    public static function isValid(string $code): bool
    {
        return (bool) preg_match('/^\d{'.(int) config('upadhyay.branch_code_length', 5).'}$/', $code);
    }
}

<?php

namespace App\Support;

use Illuminate\Http\Request;

class Pagination
{
    /** Read ?per_page= safely (default/max come from config/upadhyay.php). */
    public static function perPage(Request $request): int
    {
        $default = (int) config('upadhyay.pagination.default', 20);
        $max = (int) config('upadhyay.pagination.max', 100);

        return max(1, min((int) $request->query('per_page', $default), $max));
    }
}

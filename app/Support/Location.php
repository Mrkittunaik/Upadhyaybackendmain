<?php

namespace App\Support;

class Location
{
    /** "Hyderabad (HYD)" */
    public static function display(?string $city, ?string $code, ?string $fallback = null): ?string
    {
        if (! $city) {
            return $fallback;
        }

        return $code ? "{$city} ({$code})" : $city;
    }
}

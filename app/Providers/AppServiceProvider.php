<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Password reset links point at the frontend page.
        ResetPassword::createUrlUsing(function ($user, string $token) {
            $front = trim(explode(',', (string) config('app.frontend_url'))[0]);

            return rtrim($front, '/').'/reset-password?token='.$token.'&email='.urlencode($user->getEmailForPasswordReset());
        });

        $key = fn (Request $r) => $r->user()?->id ?: $r->ip();

        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(120)->by($key($r)));
        RateLimiter::for('auth', fn (Request $r) => Limit::perMinute(10)->by($r->ip()));
        RateLimiter::for('search', fn (Request $r) => Limit::perMinute(60)->by($key($r)));
        RateLimiter::for('uploads', fn (Request $r) => Limit::perMinute(20)->by($key($r)));
        RateLimiter::for('actions', fn (Request $r) => Limit::perMinute(30)->by($key($r)));
        RateLimiter::for('admin', fn (Request $r) => Limit::perMinute(120)->by($key($r)));
    }
}

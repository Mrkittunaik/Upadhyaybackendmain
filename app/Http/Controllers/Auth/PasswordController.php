<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        // Same response whether or not the email exists (prevents account enumeration).
        return $this->ok(null, 'If that email exists, a reset link has been sent');
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function ($user, string $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete(); // sign out everywhere
                event(new PasswordReset($user));
            },
        );

        return $status === Password::PASSWORD_RESET
            ? $this->ok(null, 'Password has been reset')
            : $this->fail('Invalid or expired reset token', 422);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    /**
     * Send password reset link via email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            activity()
                ->withProperties(['email' => $request->email, 'ip' => $request->ip()])
                ->log('Password reset link requested');
                
            return ApiResponse::success('Password reset link sent to your email');
        }

        return ApiResponse::error('Unable to send reset link. Please try again.', null, 400);
    }

    /**
     * Reset the password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                // Revoke all existing tokens for security
                $user->tokens()->delete();

                event(new PasswordReset($user));
                
                // Log password reset
                activity()
                    ->causedBy($user)
                    ->withProperties(['ip' => request()->ip()])
                    ->log('Password reset successfully');
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return ApiResponse::success('Password reset successfully. Please login with your new password.');
        }

        return ApiResponse::error('Unable to reset password. Token may be invalid or expired.', null, 400);
    }
}

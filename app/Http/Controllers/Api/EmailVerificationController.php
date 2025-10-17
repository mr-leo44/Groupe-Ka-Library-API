<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Verify email via the link clicked in email
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return ApiResponse::success('Email already verified');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
            
            activity()
                ->causedBy($request->user())
                ->log('Email verified');
        }

        return ApiResponse::success('Email verified successfully');
    }

    /**
     * Resend email verification notification
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return ApiResponse::error('Email already verified', null, 400);
        }

        $request->user()->sendEmailVerificationNotification();

        activity()
            ->causedBy($request->user())
            ->log('Email verification resent');

        return ApiResponse::success('Verification email sent. Please check your inbox.');
    }
}
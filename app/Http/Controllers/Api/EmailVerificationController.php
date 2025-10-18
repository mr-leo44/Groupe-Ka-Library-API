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
     * 
     * This endpoint is called when the user clicks the verification link in their email.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Email verified successfully"
     * }
     * 
     * @response {
     *   "success": true,
     *   "message": "Email already verified"
     * }
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
     * 
     * Request a new verification email if the original was not received.
     * Rate limited to 3 requests per minute.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Verification email sent. Please check your inbox."
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "Email already verified"
     * }
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
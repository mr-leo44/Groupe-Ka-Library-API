<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     * Ensure the user has verified their email address.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->hasVerifiedEmail()) {
            return ApiResponse::error(
                'Your email address is not verified. Please check your inbox for the verification link.',
                [
                    'verified' => false,
                    'message' => 'Email verification required'
                ],
                403
            );
        }

        return $next($request);
    }
}
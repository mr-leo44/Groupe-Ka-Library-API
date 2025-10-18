<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use Illuminate\Http\Response;
use App\Services\Auth\AuthService;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\Auth\SocialAuthService;
use App\Http\Requests\SocialLoginRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @tags Authentication
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService, 
        private SocialAuthService $socialAuthService
    ) {}

    /**
     * Register a new user
     * 
     * Creates a new user account with email/password authentication.
     * The user will receive a verification email and be assigned the 'member' role.
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "User registered successfully. Please verify your email.",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "email_verified_at": null
     *     },
     *     "token": "1|AbCdEf..."
     *   }
     * }
     * 
     * @response 422 {
     *   "success": false,
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
    public function register(RegisterRequest $request)
    {
        $payload = $request->validated();
        $result = $this->authService->register($payload);
        
        activity()
            ->causedBy($result['user'])
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ])
            ->log('User registered');
        
        return ApiResponse::success(
            'User registered successfully. Please verify your email.',
            $result,
            Response::HTTP_CREATED
        );
    }

    /**
     * Login with email and password
     * 
     * Authenticates a user and returns an access token.
     * Rate limited to 5 attempts per minute per email/IP combination.
     * 
     * @response {
     *   "success": true,
     *   "message": "Authenticated successfully",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "roles": ["member"]
     *     },
     *     "token": "1|AbCdEf..."
     *   }
     * }
     * 
     * @response 401 {
     *   "success": false,
     *   "message": "Invalid credentials"
     * }
     * 
     * @response 422 {
     *   "success": false,
     *   "message": "Too many login attempts. Please try again in X seconds.",
     *   "errors": {
     *     "email": ["Too many login attempts. Please try again in 60 seconds."]
     *   }
     * }
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        
        if (!$result) {
            $request->failedLogin();
            
            activity()
                ->withProperties([
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
                ->log('Failed login attempt');
            
            return ApiResponse::error('Invalid credentials', null, Response::HTTP_UNAUTHORIZED);
        }
        
        $request->successfulLogin();
        
        $result['user']->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);
        
        activity()
            ->causedBy($result['user'])
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_name' => $request->device_name ?? 'unknown'
            ])
            ->log('User logged in');
        
        return ApiResponse::success('Authenticated successfully', $result);
    }

    /**
     * Social login (Google/Apple)
     * 
     * Authenticate or register a user using social provider tokens.
     * If the email already exists, the social account will be linked.
     * 
     * @response {
     *   "success": true,
     *   "message": "Authenticated with social provider",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@gmail.com",
     *       "provider": "google",
     *       "avatar": "https://..."
     *     },
     *     "token": "1|AbCdEf..."
     *   }
     * }
     * 
     * @response 400 {
     *   "success": false,
     *   "message": "Social login failed"
     * }
     */
    public function socialLogin(SocialLoginRequest $request)
    {
        $data = $request->validated();
        
        try {
            $result = $this->socialAuthService->loginWithProvider(
                $data['provider'],
                $data['access_token'],
                $data['device_name'] ?? 'mobile-app'
            );
            
            $result['user']->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            
            activity()
                ->causedBy($result['user'])
                ->withProperties([
                    'provider' => $data['provider'],
                    'ip' => $request->ip(),
                ])
                ->log('User logged in via ' . $data['provider']);
            
            return ApiResponse::success('Authenticated with social provider', $result);
        } catch (\Throwable $e) {
            Log::error('Social login error: ' . $e->getMessage(), [
                'provider' => $data['provider'],
                'ip' => $request->ip()
            ]);
            return ApiResponse::error('Social login failed', null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Logout current device
     * 
     * Revokes the current access token. Other devices remain logged in.
     * Use this to logout from the current device only.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Logged out successfully",
     *   "data": null
     * }
     * 
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthenticated"
     * }
     */
    public function logout()
    {
        $user = Auth::user();
        
        if ($user) {
            $user->currentAccessToken()->delete();
            
            activity()
                ->causedBy($user)
                ->withProperties(['ip' => request()->ip()])
                ->log('User logged out');
        }
        
        return ApiResponse::success('Logged out successfully', null);
    }
    
    /**
     * Logout all devices
     * 
     * Revokes all access tokens for the authenticated user.
     * User will be logged out from all devices.
     * 
     * @authenticated
     * 
     * @response {
     *   "success": true,
     *   "message": "Logged out from all devices",
     *   "data": null
     * }
     */
    public function logoutAllDevices()
    {
        $user = Auth::user();
        
        if ($user) {
            $this->authService->logout($user);
            
            activity()
                ->causedBy($user)
                ->withProperties(['ip' => request()->ip()])
                ->log('User logged out from all devices');
        }
        
        return ApiResponse::success('Logged out from all devices', null);
    }
}
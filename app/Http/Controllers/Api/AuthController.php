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

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private SocialAuthService $socialAuthService
        ) {}

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
        return ApiResponse::success('Compte créé avec succès', $result, Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        if (!$result) {
// FIX: Increment rate limit ONLY on failure
            $request->failedLogin();
            
            // Log failed attempt
            activity()
                ->withProperties([
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
                ->log('Failed login attempt');
                
                return ApiResponse::error('Informations invalides', Response::HTTP_UNAUTHORIZED);
        }
        if (Response::HTTP_UNAUTHORIZED) {
            $request->failedLogin();
        } else {
            $request->successfulLogin();

            // Update last login tracking
        $result['user']->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);
        
        // Log successful login
        activity()
            ->causedBy($result['user'])
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_name' => $request->device_name ?? 'unknown'
            ])
            ->log('User logged in');
        }
        return ApiResponse::success('Connexion reussie', $result);
    }

    public function socialLogin(SocialLoginRequest $request)
    {
        $data = $request->validated();
        try {
            $result = $this->socialAuthService->loginWithProvider(
                $data['provider'],
                $data['access_token'],
                $data['device_name'] ?? 'mobile-app'
            );

            // Update last login
            $result['user']->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            
            // Log social login
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
            ]);            return ApiResponse::error('Social login failed', Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout()
    {
        $user = Auth::user();
 // FIX: Delete only current token, not all devices
            $user->currentAccessToken()->delete();
        // Log logout
            activity()
                ->causedBy($user)
                ->withProperties(['ip' => request()->ip()])
                ->log('User logged out');
        return ApiResponse::success('Logged out', null);
    }


    public function logoutAllDevices()
    {
        $user = Auth::user();
        
        if ($user) {
            // Delete all tokens
            $this->authService->logout($user);
            
            activity()
                ->causedBy($user)
                ->withProperties(['ip' => request()->ip()])
                ->log('User logged out from all devices');
        }

                return ApiResponse::success('Logged out from all devices', null);
    }
}

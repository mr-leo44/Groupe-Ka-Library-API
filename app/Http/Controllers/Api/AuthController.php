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

class AuthController extends Controller
{
    public function __construct(private AuthService $authService, private SocialAuthService $socialAuthService) {}

    public function register(RegisterRequest $request)
    {
        $payload = $request->validated();
        $result = $this->authService->register($payload);
        return ApiResponse::success('User registered', $result, Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        if (!$result) {
            return ApiResponse::error('Invalid credentials', Response::HTTP_UNAUTHORIZED);
        }
        return ApiResponse::success('Authenticated', $result);
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
            return ApiResponse::success('Authenticated with social provider', $result);
        } catch (\Throwable $e) {
            Log::error('Social login error: '.$e->getMessage());
            return ApiResponse::error('Social login failed', Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout()
    {
        $user = auth()->user();
        if ($user) $this->authService->logout($user);
        return ApiResponse::success('Logged out', null);
    }
}

<?php
namespace App\Services\Auth;

use App\Contracts\AuthRepositoryInterface;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;

class SocialAuthService
{
    public function __construct(
        private AuthRepositoryInterface $repository,
        private TokenService $tokenService
    ) {}

    /**
     * Provider: 'google'|'apple'
     * $accessToken: token from mobile (Google: accessToken/idToken, Apple: identityToken)
     */
    public function loginWithProvider(string $provider, string $accessToken, string $deviceName = 'book-api-token'): array
    {
        $socialUser = Socialite::driver($provider)->stateless()->userFromToken($accessToken);

        $socialData = [
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'email' => $socialUser->getEmail(),
            'name' => $socialUser->getName() ?? $socialUser->getNickname(),
            'avatar' => $socialUser->getAvatar(),
        ];

        DB::beginTransaction();
        try {
            $user = $this->repository->findOrCreateFromSocial($socialData);
            if (!$user->hasAnyRole(['admin','manager','member'])) $user->assignRole('member');
            $token = $this->tokenService->createToken($user, $deviceName);
            DB::commit();
            return ['user' => $user, 'token' => $token];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

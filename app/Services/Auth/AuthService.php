<?php
namespace App\Services\Auth;

use App\Models\User;
use App\Services\Auth\TokenService;
use Illuminate\Support\Facades\Hash;
use App\Contracts\AuthRepositoryInterface;

class AuthService
{
    public function __construct(
        private AuthRepositoryInterface $repository,
        private TokenService $tokenService
    ) {}

    public function register(array $data): array
    {
        $user = $this->repository->createUser($data);
        if (method_exists($user, 'assignRole')) $user->assignRole('member');
        $token = $this->tokenService->createToken($user, $data['device_name'] ?? 'book-api-token');
        return ['user' => $user, 'token' => $token];
    }

    public function login(array $credentials): ?array
    {
        $user = $this->repository->findByEmail($credentials['email']);
        if (!$user || !Hash::check($credentials['password'], $user->password)) return null;
        $token = $this->tokenService->createToken($user, $credentials['device_name'] ?? 'book-api-token');
        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $this->tokenService->revokeAll($user);
    }
}

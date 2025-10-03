<?php
namespace App\Services\Auth;

use App\Models\User;

class TokenService
{
    public function createToken(User $user, string $deviceName = 'book-api-token'): string
    {
        return $user->createToken($deviceName)->plainTextToken;
    }

    public function revokeAll(User $user): void
    {
        $user->tokens()->delete();
    }
}

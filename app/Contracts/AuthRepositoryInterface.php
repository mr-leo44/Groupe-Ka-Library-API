<?php
namespace App\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function createUser(array $data): User;
    public function findByEmail(string $email): ?User;
    public function findByProvider(string $provider, string $providerId): ?User;
    public function findOrCreateFromSocial(array $socialData): User;
    public function attachSocialToUser(User $user, array $socialData): User;
}

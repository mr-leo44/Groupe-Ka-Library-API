<?php
namespace App\Repositories;

use App\Contracts\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    public function createUser(array $data): User
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->city_id = $data['city_id'] ?? null;
        $user->clan_id = $data['clan_id'] ?? null;
        $user->save();
        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByProvider(string $provider, string $providerId): ?User
    {
        return User::where('provider', $provider)
                    ->where('provider_id', $providerId)
                    ->first();
    }

    public function findOrCreateFromSocial(array $socialData): User
    {
        $user = $this->findByProvider($socialData['provider'], $socialData['provider_id']);
        if ($user) return $user;

        if (!empty($socialData['email'])) {
            $user = $this->findByEmail($socialData['email']);
            if ($user) return $this->attachSocialToUser($user, $socialData);
        }

        $user = new User();
        $user->name = $socialData['name'] ?? 'User '.Str::random(6);
        $user->email = $socialData['email'] ?? Str::random(8).'@example.com';
        $user->password = Hash::make(Str::random(24));
        $user->provider = $socialData['provider'];
        $user->provider_id = $socialData['provider_id'];
        $user->avatar = $socialData['avatar'] ?? null;
        $user->city_id = $socialData['city_id'] ?? null;
        $user->clan_id = $socialData['clan_id'] ?? null;
        $user->save();

        return $user;
    }

    public function attachSocialToUser(User $user, array $socialData): User
    {
        $user->provider = $socialData['provider'];
        $user->provider_id = $socialData['provider_id'];
        if (!empty($socialData['avatar'])) $user->avatar = $socialData['avatar'];
        if (!empty($socialData['city_id'])) $user->city_id = $socialData['city_id'];
        if (!empty($socialData['clan_id'])) $user->clan_id = $socialData['clan_id'];
        $user->save();
        return $user;
    }
}

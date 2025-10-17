<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\City;
use App\Models\Clan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding users...');

        // Get random cities and clans for variety
        $cities = City::all();
        $clans = Clan::all();

        // Helper function to get random location
        $getRandomLocation = function() use ($cities, $clans) {
            return [
                'city_id' => $cities->random()?->id,
                'clan_id' => $clans->random()?->id,
            ];
        };

        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@groupeka.com'],
            array_merge([
                'name' => 'Admin Groupe Ka',
                'password' => Hash::make('Admin@123!'),
                'email_verified_at' => now(),
            ], $getRandomLocation())
        );
        
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
        
        $this->command->info('   ✅ Admin: admin@groupeka.com / Admin@123!');

        // 2. Manager Users (2)
        $managers = [
            ['email' => 'manager@groupeka.com', 'name' => 'Manager Groupe Ka'],
            ['email' => 'manager2@groupeka.com', 'name' => 'Sophie Manager'],
        ];

        foreach ($managers as $managerData) {
            $manager = User::firstOrCreate(
                ['email' => $managerData['email']],
                array_merge([
                    'name' => $managerData['name'],
                    'password' => Hash::make('Manager@123!'),
                    'email_verified_at' => now(),
                ], $getRandomLocation())
            );
            
            if (!$manager->hasRole('manager')) {
                $manager->assignRole('manager');
            }
        }
        
        $this->command->info('   ✅ Managers: manager@groupeka.com / Manager@123! (2 managers)');

        // 3. Regular Members (15 members with diverse profiles)
        $members = [
            ['name' => 'John Doe', 'email' => 'john.doe@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@example.com'],
            ['name' => 'Pierre Dupont', 'email' => 'pierre.dupont@example.com'],
            ['name' => 'Marie Martin', 'email' => 'marie.martin@example.com'],
            ['name' => 'David Wilson', 'email' => 'david.wilson@example.com'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@example.com'],
            ['name' => 'Jean Mukendi', 'email' => 'jean.mukendi@example.com'],
            ['name' => 'Grace Kabila', 'email' => 'grace.kabila@example.com'],
            ['name' => 'Patrick Nsimba', 'email' => 'patrick.nsimba@example.com'],
            ['name' => 'Christine Mbuyi', 'email' => 'christine.mbuyi@example.com'],
            ['name' => 'Emmanuel Tshisekedi', 'email' => 'emmanuel.t@example.com'],
            ['name' => 'Joséphine Kalala', 'email' => 'josephine.k@example.com'],
            ['name' => 'André Kasongo', 'email' => 'andre.kasongo@example.com'],
            ['name' => 'Brigitte Lumbu', 'email' => 'brigitte.l@example.com'],
            ['name' => 'Paul Ilunga', 'email' => 'paul.ilunga@example.com'],
        ];

        foreach ($members as $memberData) {
            $member = User::firstOrCreate(
                ['email' => $memberData['email']],
                array_merge([
                    'name' => $memberData['name'],
                    'password' => Hash::make('Member@123!'),
                    'email_verified_at' => now(),
                ], $getRandomLocation())
            );
            
            if (!$member->hasRole('member')) {
                $member->assignRole('member');
            }
        }
        
        $this->command->info('   ✅ Members: ' . count($members) . ' members created / Member@123!');

        // 4. Unverified User (for testing email verification)
        $unverified = User::firstOrCreate(
            ['email' => 'unverified@example.com'],
            array_merge([
                'name' => 'Unverified User',
                'password' => Hash::make('Member@123!'),
                'email_verified_at' => null, // Not verified
            ], $getRandomLocation())
        );
        
        if (!$unverified->hasRole('member')) {
            $unverified->assignRole('member');
        }
        
        $this->command->info('   ✅ Unverified: unverified@example.com / Member@123!');

        // 5. Social Login Users (Google & Apple)
        $socialUsers = [
            [
                'name' => 'Google User',
                'email' => 'google.user@gmail.com',
                'provider' => 'google',
                'provider_id' => 'google_' . uniqid(),
                'avatar' => 'https://lh3.googleusercontent.com/a/default-user',
            ],
            [
                'name' => 'Apple User',
                'email' => 'apple.user@icloud.com',
                'provider' => 'apple',
                'provider_id' => 'apple_' . uniqid(),
                'avatar' => 'https://cdn.apple.com/default-avatar.png',
            ],
        ];

        foreach ($socialUsers as $socialData) {
            $socialUser = User::firstOrCreate(
                ['email' => $socialData['email']],
                array_merge([
                    'name' => $socialData['name'],
                    'password' => Hash::make(bin2hex(random_bytes(16))),
                    'email_verified_at' => now(), // Social users auto-verified
                    'provider' => $socialData['provider'],
                    'provider_id' => $socialData['provider_id'],
                    'avatar' => $socialData['avatar'],
                ], $getRandomLocation())
            );
            
            if (!$socialUser->hasRole('member')) {
                $socialUser->assignRole('member');
            }
        }
        
        $this->command->info('   ✅ Social: 2 social login users (Google, Apple)');

        // 6. Test user with recent activity
        $activeUser = User::firstOrCreate(
            ['email' => 'active.user@example.com'],
            array_merge([
                'name' => 'Active Test User',
                'password' => Hash::make('Member@123!'),
                'email_verified_at' => now(),
                'last_login_at' => now()->subMinutes(5),
                'last_login_ip' => '127.0.0.1',
            ], $getRandomLocation())
        );
        
        if (!$activeUser->hasRole('member')) {
            $activeUser->assignRole('member');
        }
        
        $this->command->info('   ✅ Active: active.user@example.com / Member@123!');

        // Summary
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   Total users: ' . User::count());
        $this->command->info('   └─ Admins: ' . User::role('admin')->count());
        $this->command->info('   └─ Managers: ' . User::role('manager')->count());
        $this->command->info('   └─ Members: ' . User::role('member')->count());
        $this->command->info('   └─ Verified: ' . User::whereNotNull('email_verified_at')->count());
        $this->command->info('   └─ Unverified: ' . User::whereNull('email_verified_at')->count());
        $this->command->info('   └─ Social: ' . User::whereNotNull('provider')->count());
    }
}

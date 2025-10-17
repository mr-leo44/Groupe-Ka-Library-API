<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->info('');

        $this->call([
            RoleSeeder::class,
            CitySeeder::class,
            DioceseSeeder::class,
            DoyenneSeeder::class,
            ClanSeeder::class,
            UserSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📋 Test Credentials:');
        $this->command->info('   Admin:   admin@groupeka.com / Admin@123!');
        $this->command->info('   Manager: manager@groupeka.com / Manager@123!');
        $this->command->info('   Member:  john.doe@example.com / Member@123!');
    }
}
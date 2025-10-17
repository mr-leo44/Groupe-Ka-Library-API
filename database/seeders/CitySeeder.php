<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            // Major cities in DRC
            'Kinshasa',
            'Lubumbashi',
            'Mbuji-Mayi',
            'Kananga',
            'Kisangani',
            'Bukavu',
            'Goma',
            'Kikwit',
            'Matadi',
            'Mbandaka',
            'Kolwezi',
            'Likasi',
            'Boma',
            'Tshikapa',
            'Bandundu',
            'Butembo',
            'Mwene-Ditu',
            'Isiro',
            'Kabinda',
            'Kalemie',
        ];

        foreach ($cities as $cityName) {
            City::firstOrCreate(['name' => $cityName]);
        }

        $this->command->info('✅ Cities seeded: ' . count($cities) . ' cities created');
    }
}

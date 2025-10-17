<?php

namespace Database\Seeders;

use App\Models\Diocese;
use Illuminate\Database\Seeder;

class DioceseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dioceses = [
            'Archidiocèse de Kinshasa',
            'Diocèse de Kisantu',
            'Diocèse de Boma',
            'Diocèse de Matadi',
            'Diocèse de Kikwit',
            'Diocèse d\'Idiofa',
            'Diocèse de Kenge',
            'Diocèse d\'Inongo',
            'Diocèse de Bandundu',
            'Archidiocèse de Lubumbashi',
            'Diocèse de Kolwezi',
            'Diocèse de Kamina',
            'Diocèse de Sakania-Kipushi',
            'Diocèse de Kilwa-Kasenga',
            'Archidiocèse de Bukavu',
            'Diocèse de Butembo-Beni',
            'Diocèse de Goma',
            'Diocèse de Kasongo',
            'Diocèse d\'Uvira',
            'Archidiocèse de Kananga',
            'Diocèse de Kabinda',
            'Diocèse de Luebo',
            'Diocèse de Luiza',
            'Diocèse de Mbuji-Mayi',
            'Diocèse de Mweka',
            'Archidiocèse de Kisangani',
            'Diocèse de Bondo',
            'Diocèse de Buta',
            'Diocèse d\'Isangi',
            'Diocèse de Mahagi-Nioka',
            'Diocèse de Wamba',
        ];

        foreach ($dioceses as $dioceseName) {
            Diocese::firstOrCreate(['name' => $dioceseName]);
        }

        $this->command->info('✅ Dioceses seeded: ' . count($dioceses) . ' dioceses created');
    }
}

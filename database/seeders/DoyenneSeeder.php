<?php

namespace Database\Seeders;

use App\Models\Diocese;
use App\Models\Doyenne;
use Illuminate\Database\Seeder;

class DoyenneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get dioceses
        $kinshasa = Diocese::where('name', 'Archidiocèse de Kinshasa')->first();
        $lubumbashi = Diocese::where('name', 'Archidiocèse de Lubumbashi')->first();
        $bukavu = Diocese::where('name', 'Archidiocèse de Bukavu')->first();
        $kananga = Diocese::where('name', 'Archidiocèse de Kananga')->first();
        $kisangani = Diocese::where('name', 'Archidiocèse de Kisangani')->first();

        $doyennes = [];

        // Doyennés for Kinshasa (10 doyennés)
        if ($kinshasa) {
            $doyennes = array_merge($doyennes, [
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Masina'],
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Lemba'],
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Ngaliema'],
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Limete'],
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Matete'],
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Kalamu'],
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Kasa-Vubu'],
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Ngaba'],
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Selembao'],
                ['diocese_id' => $kinshasa->id, 'name' => 'Doyenné de Mont-Ngafula'],
            ]);
        }

        // Doyennés for Lubumbashi (6 doyennés)
        if ($lubumbashi) {
            $doyennes = array_merge($doyennes, [
                ['diocese_id' => $lubumbashi->id, 'name' => 'Doyenné de Lubumbashi Centre'],
                ['diocese_id' => $lubumbashi->id, 'name' => 'Doyenné de Kamalondo'],
                ['diocese_id' => $lubumbashi->id, 'name' => 'Doyenné de Kenya'],
                ['diocese_id' => $lubumbashi->id, 'name' => 'Doyenné de Kampemba'],
                ['diocese_id' => $lubumbashi->id, 'name' => 'Doyenné d\'Annexe'],
                ['diocese_id' => $lubumbashi->id, 'name' => 'Doyenné de Ruashi'],
            ]);
        }

        // Doyennés for Bukavu (5 doyennés)
        if ($bukavu) {
            $doyennes = array_merge($doyennes, [
                ['diocese_id' => $bukavu->id, 'name' => 'Doyenné de Bukavu Centre'],
                ['diocese_id' => $bukavu->id, 'name' => 'Doyenné d\'Ibanda'],
                ['diocese_id' => $bukavu->id, 'name' => 'Doyenné de Kadutu'],
                ['diocese_id' => $bukavu->id, 'name' => 'Doyenné de Bagira'],
                ['diocese_id' => $bukavu->id, 'name' => 'Doyenné de Walungu'],
            ]);
        }

        // Doyennés for Kananga (4 doyennés)
        if ($kananga) {
            $doyennes = array_merge($doyennes, [
                ['diocese_id' => $kananga->id, 'name' => 'Doyenné de Kananga Centre'],
                ['diocese_id' => $kananga->id, 'name' => 'Doyenné de Kananga Nord'],
                ['diocese_id' => $kananga->id, 'name' => 'Doyenné de Kananga Sud'],
                ['diocese_id' => $kananga->id, 'name' => 'Doyenné de Katoka'],
            ]);
        }

        // Doyennés for Kisangani (4 doyennés)
        if ($kisangani) {
            $doyennes = array_merge($doyennes, [
                ['diocese_id' => $kisangani->id, 'name' => 'Doyenné de Kisangani Centre'],
                ['diocese_id' => $kisangani->id, 'name' => 'Doyenné de Makiso'],
                ['diocese_id' => $kisangani->id, 'name' => 'Doyenné de Mangobo'],
                ['diocese_id' => $kisangani->id, 'name' => 'Doyenné de Tshopo'],
            ]);
        }

        foreach ($doyennes as $doyenne) {
            Doyenne::firstOrCreate($doyenne);
        }

        $this->command->info('✅ Doyennes seeded: ' . count($doyennes) . ' doyennes created');
    }
}

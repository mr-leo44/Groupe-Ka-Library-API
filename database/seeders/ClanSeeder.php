<?php

namespace Database\Seeders;

use App\Models\Clan;
use App\Models\Doyenne;
use Illuminate\Database\Seeder;

class ClanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some doyennes to attach clans
        $masina = Doyenne::where('name', 'Doyenné de Masina')->first();
        $lemba = Doyenne::where('name', 'Doyenné de Lemba')->first();
        $ngaliema = Doyenne::where('name', 'Doyenné de Ngaliema')->first();
        $limete = Doyenne::where('name', 'Doyenné de Limete')->first();
        
        $clans = [];

        // Clans for Masina (8 clans)
        if ($masina) {
            $clans = array_merge($clans, [
                ['doyenne_id' => $masina->id, 'name' => 'Clan Saint Pierre'],
                ['doyenne_id' => $masina->id, 'name' => 'Clan Saint Paul'],
                ['doyenne_id' => $masina->id, 'name' => 'Clan Saint Jean'],
                ['doyenne_id' => $masina->id, 'name' => 'Clan Sainte Marie'],
                ['doyenne_id' => $masina->id, 'name' => 'Clan Saint Joseph'],
                ['doyenne_id' => $masina->id, 'name' => 'Clan Sainte Thérèse'],
                ['doyenne_id' => $masina->id, 'name' => 'Clan Saint François'],
                ['doyenne_id' => $masina->id, 'name' => 'Clan Sainte Anne'],
            ]);
        }

        // Clans for Lemba (6 clans)
        if ($lemba) {
            $clans = array_merge($clans, [
                ['doyenne_id' => $lemba->id, 'name' => 'Clan Saint Michel'],
                ['doyenne_id' => $lemba->id, 'name' => 'Clan Saint Gabriel'],
                ['doyenne_id' => $lemba->id, 'name' => 'Clan Saint Raphaël'],
                ['doyenne_id' => $lemba->id, 'name' => 'Clan Sainte Cécile'],
                ['doyenne_id' => $lemba->id, 'name' => 'Clan Saint Antoine'],
                ['doyenne_id' => $lemba->id, 'name' => 'Clan Sainte Bernadette'],
            ]);
        }

        // Clans for Ngaliema (6 clans)
        if ($ngaliema) {
            $clans = array_merge($clans, [
                ['doyenne_id' => $ngaliema->id, 'name' => 'Clan Saint Dominique'],
                ['doyenne_id' => $ngaliema->id, 'name' => 'Clan Saint Thomas'],
                ['doyenne_id' => $ngaliema->id, 'name' => 'Clan Sainte Catherine'],
                ['doyenne_id' => $ngaliema->id, 'name' => 'Clan Saint Augustin'],
                ['doyenne_id' => $ngaliema->id, 'name' => 'Clan Sainte Monique'],
                ['doyenne_id' => $ngaliema->id, 'name' => 'Clan Saint Benoît'],
            ]);
        }

        // Clans for Limete (5 clans)
        if ($limete) {
            $clans = array_merge($clans, [
                ['doyenne_id' => $limete->id, 'name' => 'Clan Saint Charles'],
                ['doyenne_id' => $limete->id, 'name' => 'Clan Sainte Élisabeth'],
                ['doyenne_id' => $limete->id, 'name' => 'Clan Saint Martin'],
                ['doyenne_id' => $limete->id, 'name' => 'Clan Sainte Claire'],
                ['doyenne_id' => $limete->id, 'name' => 'Clan Saint Grégoire'],
            ]);
        }

        // Add clans for other doyennes
        $otherDoyennes = Doyenne::whereNotIn('name', [
            'Doyenné de Masina',
            'Doyenné de Lemba', 
            'Doyenné de Ngaliema',
            'Doyenné de Limete'
        ])->take(5)->get();

        $saintNames = [
            'Saint Luc', 'Saint Marc', 'Sainte Agnès', 'Saint Étienne',
            'Sainte Lucie', 'Saint Vincent', 'Sainte Rita', 'Saint Jude',
            'Sainte Rose', 'Saint Christophe'
        ];

        foreach ($otherDoyennes as $doyenne) {
            for ($i = 0; $i < 3; $i++) {
                $clans[] = [
                    'doyenne_id' => $doyenne->id,
                    'name' => 'Clan ' . $saintNames[array_rand($saintNames)] . ' ' . ($i + 1)
                ];
            }
        }

        foreach ($clans as $clan) {
            Clan::firstOrCreate($clan);
        }

        $this->command->info('✅ Clans seeded: ' . count($clans) . ' clans created');
    }
}

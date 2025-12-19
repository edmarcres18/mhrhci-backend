<?php

namespace Database\Seeders;

use App\Models\Principal;
use Illuminate\Database\Seeder;

class PrincipalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Principal::count() > 0) {
            return;
        }

        $principals = [
            [
                'name' => 'Medix Corp',
                'description' => 'Leading provider of hospital-grade consumables and PPE.',
                'logo' => null,
            ],
            [
                'name' => 'HealTech Industries',
                'description' => 'Medical equipment innovator delivering reliable devices.',
                'logo' => null,
            ],
            [
                'name' => 'VitalLife Partners',
                'description' => 'Specializes in patient monitoring and diagnostics solutions.',
                'logo' => null,
            ],
        ];

        foreach ($principals as $principal) {
            Principal::create($principal);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/provinces.json');

        if (!file_exists($jsonPath)) {
            $this->command->error("File not found: {$jsonPath}");
            return;
        }

        $provinces = json_decode(file_get_contents($jsonPath), true);

        if (!$provinces) {
            $this->command->error("Invalid JSON data.");
            return;
        }

        foreach ($provinces as $province) {
            \App\Models\Province::updateOrCreate(
                ['id' => $province['id']],
                ['name' => $province['name']]
            );
        }

        $this->command->info('Provinces seeded successfully.');
    }
}

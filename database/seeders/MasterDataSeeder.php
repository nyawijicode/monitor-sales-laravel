<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use App\Models\CustomerStatus;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Activity Types
        $activityTypes = [
            'Perkenalan',
            'Presentasi',
            'Demo',
            'Survei',
            'Follow Up',
        ];

        foreach ($activityTypes as $type) {
            ActivityType::firstOrCreate(['name' => $type]);
        }

        $this->command->info('Activity Types seeded.');

        // 2. Customer Statuses
        $customerStatuses = [
            ['name' => 'Lead', 'order' => 1],
            ['name' => 'Prospect', 'order' => 2],
            ['name' => 'Hot Prospect', 'order' => 3],
            ['name' => 'Deal/PO/SPK', 'order' => 4],
            ['name' => 'Pending', 'order' => 5],
            ['name' => 'Lost', 'order' => 6],
        ];

        foreach ($customerStatuses as $status) {
            CustomerStatus::updateOrCreate(
                ['name' => $status['name']],
                ['order' => $status['order']]
            );
        }

        $this->command->info('Customer Statuses seeded.');
    }
}

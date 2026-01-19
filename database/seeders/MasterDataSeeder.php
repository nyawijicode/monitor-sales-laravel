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
            'Lead',
            'Prospect',
            'Hot Prospect',
            'Deal/PO/SPK',
            'Pending',
            'Lost',
        ];

        foreach ($customerStatuses as $status) {
            CustomerStatus::firstOrCreate(['name' => $status]);
        }

        $this->command->info('Customer Statuses seeded.');
    }
}

<?php

namespace Database\Seeders;

use App\Models\PortalLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PortalLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $links = [
            [
                'title' => 'Akunting Portal',
                'url' => '/akunting',
                'icon' => 'heroicon-o-calculator',
                'badge_text' => 'Finance',
                'badge_color' => 'success',
                'description' => 'Manage financial records, invoices, and expenses.',
                'sort_order' => 1,
            ],
            [
                'title' => 'Gudang Portal',
                'url' => '/gudang',
                'icon' => 'heroicon-o-archive-box',
                'badge_text' => 'Inventory',
                'badge_color' => 'warning',
                'description' => 'Monitor stock levels, manage warehouses, and track shipments.',
                'sort_order' => 2,
            ],
            [
                'title' => 'Sales Portal',
                'url' => '/sales',
                'icon' => 'heroicon-o-presentation-chart-line',
                'badge_text' => 'Sales',
                'badge_color' => 'primary',
                'description' => 'Track sales performance, manage leads, and view reports.',
                'sort_order' => 3,
            ],
            [
                'title' => 'Teknisi Portal',
                'url' => '/teknisi',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'badge_text' => 'Technical',
                'badge_color' => 'info',
                'description' => 'Manage technical support tickets, installations, and maintenance.',
                'sort_order' => 4,
            ],
        ];

        foreach ($links as $link) {
            PortalLink::updateOrCreate(
                ['url' => $link['url']], // Use URL as unique identifier to avoid duplicates
                [
                    'title' => $link['title'],
                    'slug' => Str::slug($link['title']),
                    'description' => $link['description'],
                    'icon' => $link['icon'],
                    'badge_text' => $link['badge_text'],
                    'badge_color' => $link['badge_color'],
                    'sort_order' => $link['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Portal Links seeded.');
    }
}

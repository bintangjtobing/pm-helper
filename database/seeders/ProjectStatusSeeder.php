<?php

namespace Database\Seeders;

use App\Models\ProjectStatus;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    private array $data = [
        [
            'name' => 'Planning',
            'color' => '#9CA3AF',
            'is_default' => true,
        ],
        [
            'name' => 'Active',
            'color' => '#10B981',
            'is_default' => false,
        ],
        [
            'name' => 'On Hold',
            'color' => '#F59E0B',
            'is_default' => false,
        ],
        [
            'name' => 'Completed',
            'color' => '#3B82F6',
            'is_default' => false,
        ],
        [
            'name' => 'Cancelled',
            'color' => '#EF4444',
            'is_default' => false,
        ],
        [
            'name' => 'Archived',
            'color' => '#6B7280',
            'is_default' => false,
        ],
    ];

    public function run(): void
    {
        foreach ($this->data as $item) {
            ProjectStatus::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}

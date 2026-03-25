<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    private array $data = [
        // Development Layer
        [
            'name' => 'Backlog',
            'color' => '#9CA3AF',
            'is_default' => true,
            'order' => 1
        ],
        [
            'name' => 'Ready for Dev',
            'color' => '#6366F1',
            'is_default' => false,
            'order' => 2
        ],
        [
            'name' => 'In Progress',
            'color' => '#3B82F6',
            'is_default' => false,
            'order' => 3
        ],
        [
            'name' => 'Code Review',
            'color' => '#8B5CF6',
            'is_default' => false,
            'order' => 4
        ],
        // QA Layer
        [
            'name' => 'Ready for QA',
            'color' => '#F59E0B',
            'is_default' => false,
            'order' => 5
        ],
        [
            'name' => 'QA Testing',
            'color' => '#F97316',
            'is_default' => false,
            'order' => 6
        ],
        [
            'name' => 'QA Failed',
            'color' => '#EF4444',
            'is_default' => false,
            'order' => 7
        ],
        [
            'name' => 'Fixing',
            'color' => '#EC4899',
            'is_default' => false,
            'order' => 8
        ],
        [
            'name' => 'Retest',
            'color' => '#F59E0B',
            'is_default' => false,
            'order' => 9
        ],
        [
            'name' => 'QA Passed',
            'color' => '#10B981',
            'is_default' => false,
            'order' => 10
        ],
        // Business Layer
        [
            'name' => 'Waiting Approval',
            'color' => '#6366F1',
            'is_default' => false,
            'order' => 11
        ],
        [
            'name' => 'Approved',
            'color' => '#22C55E',
            'is_default' => false,
            'order' => 12
        ],
        [
            'name' => 'Rejected',
            'color' => '#EF4444',
            'is_default' => false,
            'order' => 13
        ],
        [
            'name' => 'Ready for Release',
            'color' => '#0EA5E9',
            'is_default' => false,
            'order' => 14
        ],
        [
            'name' => 'Released',
            'color' => '#059669',
            'is_default' => false,
            'order' => 15
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $item) {
            TicketStatus::firstOrCreate($item);
        }
    }
}

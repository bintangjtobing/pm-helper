<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class RequestSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Add "Request" ticket type
        TicketType::firstOrCreate(
            ['name' => 'Request'],
            [
                'color' => '#8b5cf6',
                'icon' => 'heroicon-o-clipboard-list',
                'is_default' => false,
                'description' => 'Internal request from any department — requires PM approval before execution',
            ]
        );

        // Add request-specific statuses
        $maxOrder = TicketStatus::max('order') ?? 0;

        $statuses = [
            ['name' => 'Request', 'color' => '#8b5cf6', 'order' => $maxOrder + 1],
            ['name' => 'Under Review', 'color' => '#f59e0b', 'order' => $maxOrder + 2],
            ['name' => 'Approved', 'color' => '#22c55e', 'order' => $maxOrder + 3],
            ['name' => 'Rejected', 'color' => '#ef4444', 'order' => $maxOrder + 4],
        ];

        foreach ($statuses as $status) {
            TicketStatus::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }

        $this->command->info('Request system seeded: 1 ticket type + 4 statuses');
    }
}

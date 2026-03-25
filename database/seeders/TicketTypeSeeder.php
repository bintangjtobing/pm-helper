<?php

namespace Database\Seeders;

use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    private array $data = [
        // Core Types
        [
            'name' => 'Bug',
            'icon' => 'heroicon-o-x-circle',
            'color' => '#EF4444',
            'is_default' => false,
            'description' => 'Error or defect. Behavior does not match expected outcome.',
        ],
        [
            'name' => 'Task',
            'icon' => 'heroicon-o-check-circle',
            'color' => '#3B82F6',
            'is_default' => true,
            'description' => 'General work item. Not specific to a feature or bug. E.g. server setup, dependency updates.',
        ],
        [
            'name' => 'Feature',
            'icon' => 'heroicon-o-sparkles',
            'color' => '#8B5CF6',
            'is_default' => false,
            'description' => 'New user-facing functionality. E.g. add Google login, add trading chart.',
        ],
        [
            'name' => 'Improvement',
            'icon' => 'heroicon-o-trending-up',
            'color' => '#10B981',
            'is_default' => false,
            'description' => 'Enhancement to an existing feature. Better UX, performance optimization, refactoring.',
        ],
        [
            'name' => 'Sub-task',
            'icon' => 'heroicon-o-collection',
            'color' => '#6B7280',
            'is_default' => false,
            'description' => 'Breakdown of a larger task. Used for work decomposition in bigger teams.',
        ],
        // Extended Types
        [
            'name' => 'Epic',
            'icon' => 'heroicon-o-lightning-bolt',
            'color' => '#F59E0B',
            'is_default' => false,
            'description' => 'Large feature set or initiative spanning multiple tickets. E.g. "Trading System Revamp".',
        ],
        [
            'name' => 'Spike',
            'icon' => 'heroicon-o-beaker',
            'color' => '#06B6D4',
            'is_default' => false,
            'description' => 'Technical research or exploration. Time-boxed investigation. E.g. "Test Redis vs Kafka".',
        ],
        [
            'name' => 'Hotfix',
            'icon' => 'heroicon-o-fire',
            'color' => '#DC2626',
            'is_default' => false,
            'description' => 'Urgent production fix. Bypasses normal flow and deploys directly to production.',
        ],
        [
            'name' => 'QA / Test Case',
            'icon' => 'heroicon-o-clipboard-check',
            'color' => '#0EA5E9',
            'is_default' => false,
            'description' => 'Testing work item. Test case creation, execution, or automation.',
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
            TicketType::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        // Soft-delete the old "Evolution" type (replaced by "Improvement")
        TicketType::where('name', 'Evolution')->whereNull('deleted_at')->update(['deleted_at' => now()]);
    }
}

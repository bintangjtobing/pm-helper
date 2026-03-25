<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    private array $data = [
        // Development Layer — Dev, PM, Super Admin can set these
        ['name' => 'Backlog',        'color' => '#9CA3AF', 'is_default' => true,  'order' => 1,  'role_group' => 'any'],
        ['name' => 'Ready for Dev',  'color' => '#6366F1', 'is_default' => false, 'order' => 2,  'role_group' => 'dev'],
        ['name' => 'In Progress',    'color' => '#3B82F6', 'is_default' => false, 'order' => 3,  'role_group' => 'dev'],
        ['name' => 'Code Review',    'color' => '#8B5CF6', 'is_default' => false, 'order' => 4,  'role_group' => 'dev'],
        // QA Layer — QA, PM, Super Admin can set these
        ['name' => 'Ready for QA',   'color' => '#F59E0B', 'is_default' => false, 'order' => 5,  'role_group' => 'dev'],
        ['name' => 'QA Testing',     'color' => '#F97316', 'is_default' => false, 'order' => 6,  'role_group' => 'qa'],
        ['name' => 'QA Failed',      'color' => '#EF4444', 'is_default' => false, 'order' => 7,  'role_group' => 'qa'],
        ['name' => 'Fixing',         'color' => '#EC4899', 'is_default' => false, 'order' => 8,  'role_group' => 'dev'],
        ['name' => 'Retest',         'color' => '#F59E0B', 'is_default' => false, 'order' => 9,  'role_group' => 'qa'],
        ['name' => 'QA Passed',      'color' => '#10B981', 'is_default' => false, 'order' => 10, 'role_group' => 'qa'],
        // Business Layer — PM, DevOps, Super Admin can set these
        ['name' => 'Waiting Approval',  'color' => '#6366F1', 'is_default' => false, 'order' => 11, 'role_group' => 'business'],
        ['name' => 'Approved',          'color' => '#22C55E', 'is_default' => false, 'order' => 12, 'role_group' => 'business'],
        ['name' => 'Rejected',          'color' => '#EF4444', 'is_default' => false, 'order' => 13, 'role_group' => 'business'],
        ['name' => 'Ready for Release', 'color' => '#0EA5E9', 'is_default' => false, 'order' => 14, 'role_group' => 'business'],
        ['name' => 'Released',          'color' => '#059669', 'is_default' => false, 'order' => 15, 'role_group' => 'business'],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $item) {
            TicketStatus::updateOrCreate(
                ['name' => $item['name'], 'project_id' => null],
                $item
            );
        }
    }
}

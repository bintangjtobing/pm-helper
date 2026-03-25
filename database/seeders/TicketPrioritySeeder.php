<?php

namespace Database\Seeders;

use App\Models\TicketPriority;
use Illuminate\Database\Seeder;

class TicketPrioritySeeder extends Seeder
{
    private array $data = [
        [
            'name' => 'P0 — Critical / Blocker',
            'level' => 'P0',
            'color' => '#DC2626',
            'is_default' => false,
            'description' => 'System is down, business operations halted, or security breach detected.',
            'examples' => 'Production down, payment processing completely failed, security breach, core feature entirely unusable.',
            'action' => 'Hotfix immediately. All other work stops until resolved.',
        ],
        [
            'name' => 'P1 — High',
            'level' => 'P1',
            'color' => '#F97316',
            'is_default' => false,
            'description' => 'Major feature is broken or severely impacted. Large number of users affected.',
            'examples' => 'Bug in core feature (checkout, login, trading), inaccurate data output, many users impacted.',
            'action' => 'Include in current sprint. Top priority after P0.',
        ],
        [
            'name' => 'P2 — Medium',
            'level' => 'P2',
            'color' => '#EAB308',
            'is_default' => true,
            'description' => 'Issue exists but a workaround is available. Feature is usable but degraded.',
            'examples' => 'Bug with known workaround, poor UX but still functional, edge case issues.',
            'action' => 'Schedule for next sprint backlog.',
        ],
        [
            'name' => 'P3 — Low',
            'level' => 'P3',
            'color' => '#22C55E',
            'is_default' => false,
            'description' => 'Minor issue, cosmetic problem, or nice-to-have improvement.',
            'examples' => 'UI typo, minor layout issue, small enhancement request.',
            'action' => 'Address when capacity allows.',
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
            TicketPriority::updateOrCreate(
                ['level' => $item['level']],
                $item
            );
        }
    }
}

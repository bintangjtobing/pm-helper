<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class DepartmentPositionSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            // CORE DEPARTMENTS
            [
                'name' => 'Executive / Leadership',
                'color' => '#DC2626',
                'category' => 'core',
                'description' => 'Strategic direction & decision making',
                'sort_order' => 1,
                'positions' => [
                    ['name' => 'CEO', 'level' => 4, 'sort_order' => 1],
                    ['name' => 'COO', 'level' => 4, 'sort_order' => 2],
                    ['name' => 'CTO', 'level' => 4, 'sort_order' => 3],
                    ['name' => 'CMO', 'level' => 4, 'sort_order' => 4],
                    ['name' => 'Head of Operations', 'level' => 3, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Sales & Business Development',
                'color' => '#EA580C',
                'category' => 'core',
                'description' => 'Revenue generation',
                'sort_order' => 2,
                'positions' => [
                    ['name' => 'Head of Sales', 'level' => 3, 'sort_order' => 1],
                    ['name' => 'Business Development Manager', 'level' => 2, 'sort_order' => 2],
                    ['name' => 'Sales Executive', 'level' => 0, 'sort_order' => 3],
                    ['name' => 'Account Executive', 'level' => 0, 'sort_order' => 4],
                    ['name' => 'Partnership Manager', 'level' => 2, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Account Management',
                'color' => '#2563EB',
                'category' => 'core',
                'description' => 'Client handling & retention',
                'sort_order' => 3,
                'positions' => [
                    ['name' => 'Account Manager', 'level' => 2, 'sort_order' => 1],
                    ['name' => 'Account Executive', 'level' => 0, 'sort_order' => 2],
                    ['name' => 'Client Success Manager', 'level' => 2, 'sort_order' => 3],
                    ['name' => 'Customer Support', 'level' => 0, 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Digital Marketing',
                'color' => '#7C3AED',
                'category' => 'core',
                'description' => 'Marketing execution',
                'sort_order' => 4,
                'positions' => [
                    // SEO Team
                    ['name' => 'SEO Specialist', 'sub_division' => 'SEO Team', 'level' => 0, 'sort_order' => 1],
                    ['name' => 'Technical SEO', 'sub_division' => 'SEO Team', 'level' => 0, 'sort_order' => 2],
                    ['name' => 'Content SEO Strategist', 'sub_division' => 'SEO Team', 'level' => 1, 'sort_order' => 3],
                    // Paid Ads Team
                    ['name' => 'Performance Marketer', 'sub_division' => 'Paid Ads Team', 'level' => 0, 'sort_order' => 4],
                    ['name' => 'Media Buyer', 'sub_division' => 'Paid Ads Team', 'level' => 0, 'sort_order' => 5],
                    ['name' => 'Ads Optimizer', 'sub_division' => 'Paid Ads Team', 'level' => 0, 'sort_order' => 6],
                    // Social Media Team
                    ['name' => 'Social Media Specialist', 'sub_division' => 'Social Media Team', 'level' => 0, 'sort_order' => 7],
                    ['name' => 'Content Planner', 'sub_division' => 'Social Media Team', 'level' => 0, 'sort_order' => 8],
                    ['name' => 'Community Manager', 'sub_division' => 'Social Media Team', 'level' => 1, 'sort_order' => 9],
                    // Content Team
                    ['name' => 'Content Writer', 'sub_division' => 'Content Team', 'level' => 0, 'sort_order' => 10],
                    ['name' => 'Copywriter', 'sub_division' => 'Content Team', 'level' => 0, 'sort_order' => 11],
                    ['name' => 'Editor', 'sub_division' => 'Content Team', 'level' => 1, 'sort_order' => 12],
                ],
            ],
            [
                'name' => 'Creative / Design',
                'color' => '#16A34A',
                'category' => 'core',
                'description' => 'Visual & branding',
                'sort_order' => 5,
                'positions' => [
                    ['name' => 'Creative Director', 'level' => 3, 'sort_order' => 1],
                    ['name' => 'Graphic Designer', 'level' => 0, 'sort_order' => 2],
                    ['name' => 'Motion Designer', 'level' => 0, 'sort_order' => 3],
                    ['name' => 'Video Editor', 'level' => 0, 'sort_order' => 4],
                    ['name' => 'UI/UX Designer', 'level' => 0, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Tech / Development',
                'color' => '#CA8A04',
                'category' => 'core',
                'description' => 'Build systems & landing pages',
                'sort_order' => 6,
                'positions' => [
                    ['name' => 'Frontend Developer', 'level' => 0, 'sort_order' => 1],
                    ['name' => 'Backend Developer', 'level' => 0, 'sort_order' => 2],
                    ['name' => 'Full Stack Developer', 'level' => 0, 'sort_order' => 3],
                    ['name' => 'WordPress Developer', 'level' => 0, 'sort_order' => 4],
                    ['name' => 'QA Engineer', 'level' => 0, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Data & Analytics',
                'color' => '#171717',
                'category' => 'core',
                'description' => 'Data-driven decision making',
                'sort_order' => 7,
                'positions' => [
                    ['name' => 'Data Analyst', 'level' => 0, 'sort_order' => 1],
                    ['name' => 'Marketing Analyst', 'level' => 0, 'sort_order' => 2],
                    ['name' => 'BI Specialist', 'level' => 0, 'sort_order' => 3],
                    ['name' => 'Tracking Specialist', 'level' => 0, 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Operations',
                'color' => '#F5F5F4',
                'category' => 'core',
                'description' => 'Workflow & efficiency',
                'sort_order' => 8,
                'positions' => [
                    ['name' => 'Project Manager', 'level' => 2, 'sort_order' => 1],
                    ['name' => 'Scrum Master', 'level' => 2, 'sort_order' => 2],
                    ['name' => 'Operations Manager', 'level' => 2, 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Finance & Legal',
                'color' => '#78716C',
                'category' => 'core',
                'description' => 'Finance & compliance',
                'sort_order' => 9,
                'positions' => [
                    ['name' => 'Finance Manager', 'level' => 2, 'sort_order' => 1],
                    ['name' => 'Accountant', 'level' => 0, 'sort_order' => 2],
                    ['name' => 'Legal Officer', 'level' => 0, 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'HR / People',
                'color' => '#059669',
                'category' => 'core',
                'description' => 'Hiring & culture',
                'sort_order' => 10,
                'positions' => [
                    ['name' => 'HR Manager', 'level' => 2, 'sort_order' => 1],
                    ['name' => 'Talent Acquisition', 'level' => 0, 'sort_order' => 2],
                    ['name' => 'HR Generalist', 'level' => 0, 'sort_order' => 3],
                ],
            ],
            // ADVANCED DEPARTMENTS
            [
                'name' => 'Growth / Innovation',
                'color' => '#F97316',
                'category' => 'advanced',
                'description' => 'Growth hacking & experimentation',
                'sort_order' => 11,
                'positions' => [
                    ['name' => 'Growth Hacker', 'level' => 0, 'sort_order' => 1],
                    ['name' => 'Experimentation Lead', 'level' => 1, 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Product / Platform',
                'color' => '#F97316',
                'category' => 'advanced',
                'description' => 'Product management & design',
                'sort_order' => 12,
                'positions' => [
                    ['name' => 'Product Manager', 'level' => 2, 'sort_order' => 1],
                    ['name' => 'Product Designer', 'level' => 0, 'sort_order' => 2],
                ],
            ],
            [
                'name' => 'Automation / AI',
                'color' => '#F97316',
                'category' => 'advanced',
                'description' => 'AI & marketing automation',
                'sort_order' => 13,
                'positions' => [
                    ['name' => 'AI Engineer', 'level' => 0, 'sort_order' => 1],
                    ['name' => 'Marketing Automation Specialist', 'level' => 0, 'sort_order' => 2],
                ],
            ],
        ];

        foreach ($departments as $deptData) {
            $positions = $deptData['positions'];
            unset($deptData['positions']);

            $dept = Department::firstOrCreate(['name' => $deptData['name']], $deptData);

            foreach ($positions as $posData) {
                Position::firstOrCreate(
                    ['name' => $posData['name'], 'department_id' => $dept->id],
                    array_merge($posData, ['department_id' => $dept->id])
                );
            }
        }
    }
}

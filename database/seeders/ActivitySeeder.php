<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Activities organized by lifecycle phase.
     * Each parent has optional sub-activities for granular tracking.
     */
    private array $data = [
        [
            'name' => 'Requirement Analysis',
            'description' => 'Gathering, analyzing, and documenting requirements from stakeholders.',
            'sort_order' => 1,
            'children' => [
                ['name' => 'Stakeholder Interview', 'description' => 'Meeting with stakeholders to gather requirements.'],
                ['name' => 'User Story Writing', 'description' => 'Writing user stories and acceptance criteria.'],
                ['name' => 'Backlog Refinement', 'description' => 'Prioritizing and refining the product backlog.'],
            ],
        ],
        [
            'name' => 'Design',
            'description' => 'UI/UX design, system architecture, and technical design.',
            'sort_order' => 2,
            'children' => [
                ['name' => 'UI/UX Design', 'description' => 'User interface and experience design work.'],
                ['name' => 'System Architecture', 'description' => 'Technical architecture and system design.'],
                ['name' => 'Database Design', 'description' => 'Schema design, data modeling, and ERD.'],
                ['name' => 'API Design', 'description' => 'API contract, endpoint design, and documentation.'],
            ],
        ],
        [
            'name' => 'Development',
            'description' => 'Building features, writing code, and implementing solutions.',
            'sort_order' => 3,
            'children' => [
                ['name' => 'Frontend Development', 'description' => 'Client-side UI implementation.'],
                ['name' => 'Backend Development', 'description' => 'Server-side logic and API implementation.'],
                ['name' => 'Integration', 'description' => 'Integrating with third-party services or APIs.'],
            ],
        ],
        [
            'name' => 'Code Review',
            'description' => 'Reviewing pull requests, providing feedback, and ensuring code quality.',
            'sort_order' => 4,
        ],
        [
            'name' => 'Testing',
            'description' => 'QA process — manual testing, automated testing, regression testing.',
            'sort_order' => 5,
            'children' => [
                ['name' => 'Manual Testing', 'description' => 'Manual test case execution and exploratory testing.'],
                ['name' => 'Automated Testing', 'description' => 'Writing and running automated test suites.'],
                ['name' => 'Regression Testing', 'description' => 'Verifying existing functionality after changes.'],
                ['name' => 'Performance Testing', 'description' => 'Load testing, stress testing, and benchmarking.'],
            ],
        ],
        [
            'name' => 'Bug Fixing',
            'description' => 'Investigating, diagnosing, and fixing defects.',
            'sort_order' => 6,
        ],
        [
            'name' => 'Refactoring',
            'description' => 'Restructuring existing code without changing external behavior.',
            'sort_order' => 7,
        ],
        [
            'name' => 'Deployment',
            'description' => 'Releasing code to staging or production environments.',
            'sort_order' => 8,
            'children' => [
                ['name' => 'Staging Deployment', 'description' => 'Deploying to staging/QA environment.'],
                ['name' => 'Production Deployment', 'description' => 'Deploying to production environment.'],
                ['name' => 'Rollback', 'description' => 'Reverting a deployment due to issues.'],
            ],
        ],
        [
            'name' => 'Monitoring',
            'description' => 'Post-release monitoring, alerting, and incident response.',
            'sort_order' => 9,
        ],
        [
            'name' => 'Maintenance',
            'description' => 'Ongoing system maintenance — dependency updates, server patching, cleanup.',
            'sort_order' => 10,
        ],
        [
            'name' => 'Research',
            'description' => 'Technical research, spike work, and proof of concept.',
            'sort_order' => 11,
        ],
        [
            'name' => 'Documentation',
            'description' => 'Writing technical docs, API docs, runbooks, and knowledge base articles.',
            'sort_order' => 12,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $sortOrder => $item) {
            $children = $item['children'] ?? [];
            unset($item['children']);

            $item['level'] = 0;
            $item['parent_id'] = null;

            $parent = Activity::updateOrCreate(
                ['name' => $item['name'], 'parent_id' => null],
                $item
            );

            foreach ($children as $childOrder => $child) {
                $child['parent_id'] = $parent->id;
                $child['level'] = 1;
                $child['sort_order'] = $childOrder + 1;

                Activity::updateOrCreate(
                    ['name' => $child['name'], 'parent_id' => $parent->id],
                    $child
                );
            }
        }

        // Soft-delete old activities that are no longer relevant
        $validNames = collect($this->data)->pluck('name')->toArray();
        Activity::whereNull('parent_id')
            ->whereNotIn('name', $validNames)
            ->whereIn('name', ['Programming', 'Learning', 'Other'])
            ->delete();
    }
}

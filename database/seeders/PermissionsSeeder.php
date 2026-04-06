<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    private array $modules = [
        'permission', 'project', 'project status', 'role', 'ticket',
        'ticket priority', 'ticket status', 'ticket type', 'user',
        'activity', 'sprint', 'comment', 'customer feedback', 'weekly report',
        'daily report', 'discussion'
    ];

    private array $pluralActions = [
        'List'
    ];

    private array $singularActions = [
        'View', 'Create', 'Update', 'Delete'
    ];

    private array $extraPermissions = [
        'Manage general settings', 'Import from Jira',
        'List timesheet data', 'View timesheet dashboard',
        'Approve request', 'Reject request', 'Convert request',
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create all permissions
        foreach ($this->modules as $module) {
            $plural = Str::plural($module);
            $singular = $module;
            foreach ($this->pluralActions as $action) {
                Permission::firstOrCreate(['name' => $action . ' ' . $plural]);
            }
            foreach ($this->singularActions as $action) {
                Permission::firstOrCreate(['name' => $action . ' ' . $singular]);
            }
        }

        foreach ($this->extraPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create all roles
        $this->createSuperAdminRole();
        $this->createProjectManagerRole();
        $this->createDeveloperRole();
        $this->createQARole();
        $this->createDevOpsRole();
        $this->createStakeholderRole();
        // New company-wide roles
        $this->createExecutiveRole();
        $this->createSalesRole();
        $this->createAccountManagerRole();
        $this->createDigitalMarketerRole();
        $this->createContentWriterRole();
        $this->createDesignerRole();
        $this->createDataAnalystRole();
        $this->createOperationsRole();
        $this->createHRRole();
        $this->createFinanceRole();

        // Set Developer as default role for new users
        $devRole = Role::where('name', 'Developer')->first();
        $settings = app(GeneralSettings::class);
        $settings->default_role = $devRole->id;
        $settings->save();

        // Assign Super Admin to first user
        if ($user = User::first()) {
            $user->syncRoles(['Super Admin']);
        }
    }

    /**
     * Super Admin — Full system control
     */
    private function createSuperAdminRole()
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $role->syncPermissions(Permission::all()->pluck('name')->toArray());
    }

    /**
     * Project Manager / Product Owner — Project & delivery control
     */
    private function createProjectManagerRole()
    {
        $role = Role::firstOrCreate(['name' => 'Project Manager']);

        $permissions = [
            // Project
            'List projects', 'View project', 'Create project', 'Update project',
            'List project statuses', 'View project status', 'Update project status',
            // Sprint
            'List sprints', 'View sprint', 'Create sprint', 'Update sprint',
            // Ticket — full control except delete
            'List tickets', 'View ticket', 'Create ticket', 'Update ticket',
            'List ticket priorities', 'View ticket priority', 'Update ticket priority',
            'List ticket statuses', 'View ticket status', 'Update ticket status',
            'List ticket types', 'View ticket type',
            // Comment
            'List comments', 'View comment', 'Create comment', 'Update comment',
            // Activity & Timesheet
            'List activities', 'View activity',
            'List timesheet data', 'View timesheet dashboard',
            // Customer Feedback
            'List customer feedbacks', 'View customer feedback', 'Update customer feedback',
            // Weekly Report
            'List weekly reports', 'View weekly report', 'Create weekly report',
            'Update weekly report', 'Delete weekly report',
            // Daily Report
            'List daily reports', 'View daily report', 'Create daily report',
            'Update daily report', 'Delete daily report',
            // Discussion
            'List discussions', 'View discussion', 'Create discussion',
            'Update discussion', 'Delete discussion',
            // Request management
            'Approve request', 'Reject request', 'Convert request',
            // User — view only
            'List users', 'View user',
        ];

        $existing = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
        $role->syncPermissions($existing);
    }

    /**
     * Developer — Task execution
     */
    private function createDeveloperRole()
    {
        $role = Role::firstOrCreate(['name' => 'Developer']);

        $permissions = [
            // Project — view only
            'List projects', 'View project',
            'List project statuses', 'View project status',
            // Sprint — view only
            'List sprints', 'View sprint',
            // Ticket — view, create, update (no delete)
            'List tickets', 'View ticket', 'Create ticket', 'Update ticket',
            'List ticket priorities', 'View ticket priority',
            'List ticket statuses', 'View ticket status',
            'List ticket types', 'View ticket type',
            // Comment
            'List comments', 'View comment', 'Create comment', 'Update comment',
            // Activity
            'List activities', 'View activity',
            // Weekly Report
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
            // Daily Report
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            // Discussion
            'List discussions', 'View discussion', 'Create discussion', 'Update discussion',
            // Timesheet — own data
            'List timesheet data',
        ];

        $existing = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
        $role->syncPermissions($existing);
    }

    /**
     * QA / Tester — Quality validation
     */
    private function createQARole()
    {
        $role = Role::firstOrCreate(['name' => 'QA / Tester']);

        $permissions = [
            // Project — view only
            'List projects', 'View project',
            'List project statuses', 'View project status',
            // Sprint — view only
            'List sprints', 'View sprint',
            // Ticket — view, update status (critical for QA flow)
            'List tickets', 'View ticket', 'Update ticket',
            'List ticket statuses', 'View ticket status', 'Update ticket status',
            'List ticket priorities', 'View ticket priority',
            'List ticket types', 'View ticket type',
            // Comment — can create bug reports as comments
            'List comments', 'View comment', 'Create comment', 'Update comment',
            // Activity
            'List activities', 'View activity',
            // Customer Feedback
            'List customer feedbacks', 'View customer feedback',
            'Create customer feedback', 'Update customer feedback',
            // Weekly Report
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
            // Daily Report
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            // Discussion
            'List discussions', 'View discussion', 'Create discussion', 'Update discussion',
            // Timesheet
            'List timesheet data',
        ];

        $existing = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
        $role->syncPermissions($existing);
    }

    /**
     * DevOps / Release Manager — Deployment & infrastructure
     */
    private function createDevOpsRole()
    {
        $role = Role::firstOrCreate(['name' => 'DevOps']);

        $permissions = [
            // Project — view + update status (for release)
            'List projects', 'View project',
            'List project statuses', 'View project status', 'Update project status',
            // Sprint — view only
            'List sprints', 'View sprint',
            // Ticket — view + update status (for deployment tracking)
            'List tickets', 'View ticket', 'Update ticket',
            'List ticket statuses', 'View ticket status', 'Update ticket status',
            'List ticket priorities', 'View ticket priority',
            'List ticket types', 'View ticket type',
            // Comment
            'List comments', 'View comment', 'Create comment',
            // Activity
            'List activities', 'View activity',
            // Weekly Report
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
            // Daily Report
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            // Discussion
            'List discussions', 'View discussion', 'Create discussion', 'Update discussion',
            // Timesheet
            'List timesheet data', 'View timesheet dashboard',
        ];

        $existing = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
        $role->syncPermissions($existing);
    }

    /**
     * Stakeholder / Client — Visibility only
     */
    private function createStakeholderRole()
    {
        $role = Role::firstOrCreate(['name' => 'Stakeholder']);

        $permissions = [
            // View-only across the board
            'List projects', 'View project',
            'View project status', 'List project statuses',
            'List tickets', 'View ticket',
            'View ticket status', 'View ticket priority', 'View ticket type',
            'List sprints', 'View sprint',
            // Customer Feedback — can submit
            'List customer feedbacks', 'View customer feedback',
            'Create customer feedback',
            // Weekly Report — view only + give feedback via UI
            'List weekly reports', 'View weekly report',
            // Daily Report — view only
            'List daily reports', 'View daily report',
            // Discussion — view + participate
            'List discussions', 'View discussion', 'Create discussion',
        ];

        $existing = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
        $role->syncPermissions($existing);
    }

    /**
     * Executive / Director — Strategic oversight, view-only + approve requests
     */
    private function createExecutiveRole()
    {
        $role = Role::firstOrCreate(['name' => 'Executive']);
        $permissions = [
            'List projects', 'View project',
            'List project statuses', 'View project status',
            'List tickets', 'View ticket',
            'View ticket status', 'View ticket priority', 'View ticket type',
            'List sprints', 'View sprint',
            'List activities', 'View activity',
            'List timesheet data', 'View timesheet dashboard',
            'List customer feedbacks', 'View customer feedback',
            'List weekly reports', 'View weekly report',
            'List daily reports', 'View daily report',
            'List discussions', 'View discussion', 'Create discussion',
            'Approve request', 'Reject request', 'Convert request',
            'List users', 'View user',
            'Create ticket',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }

    /**
     * Sales / Business Development — Revenue generation
     */
    private function createSalesRole()
    {
        $role = Role::firstOrCreate(['name' => 'Sales']);
        $permissions = [
            'List projects', 'View project',
            'List tickets', 'View ticket', 'Create ticket',
            'View ticket status', 'View ticket priority', 'View ticket type',
            'List comments', 'View comment', 'Create comment',
            'List customer feedbacks', 'View customer feedback', 'Create customer feedback',
            'List discussions', 'View discussion', 'Create discussion',
            'List daily reports', 'View daily report', 'Create daily report',
            'List weekly reports', 'View weekly report',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }

    /**
     * Account Manager — Client handling & communication
     */
    private function createAccountManagerRole()
    {
        $role = Role::firstOrCreate(['name' => 'Account Manager']);
        $permissions = [
            'List projects', 'View project', 'Update project',
            'List tickets', 'View ticket', 'Create ticket', 'Update ticket',
            'List ticket statuses', 'View ticket status',
            'List ticket priorities', 'View ticket priority',
            'List ticket types', 'View ticket type',
            'List sprints', 'View sprint',
            'List comments', 'View comment', 'Create comment', 'Update comment',
            'List customer feedbacks', 'View customer feedback',
            'Create customer feedback', 'Update customer feedback',
            'List discussions', 'View discussion', 'Create discussion', 'Update discussion',
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
            'List activities', 'View activity',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }

    /**
     * Digital Marketer — SEO, Ads, Social Media
     */
    private function createDigitalMarketerRole()
    {
        $role = Role::firstOrCreate(['name' => 'Digital Marketer']);
        $permissions = [
            'List projects', 'View project',
            'List tickets', 'View ticket', 'Create ticket', 'Update ticket',
            'View ticket status', 'View ticket priority', 'View ticket type',
            'List comments', 'View comment', 'Create comment',
            'List activities', 'View activity',
            'List discussions', 'View discussion', 'Create discussion',
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }

    /**
     * Content Writer / Copywriter
     */
    private function createContentWriterRole()
    {
        $role = Role::firstOrCreate(['name' => 'Content Writer']);
        $permissions = [
            'List projects', 'View project',
            'List tickets', 'View ticket', 'Create ticket', 'Update ticket',
            'View ticket status', 'View ticket priority', 'View ticket type',
            'List comments', 'View comment', 'Create comment',
            'List discussions', 'View discussion', 'Create discussion',
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }

    /**
     * Designer / Creative
     */
    private function createDesignerRole()
    {
        $role = Role::firstOrCreate(['name' => 'Designer']);
        $permissions = [
            'List projects', 'View project',
            'List tickets', 'View ticket', 'Create ticket', 'Update ticket',
            'View ticket status', 'View ticket priority', 'View ticket type',
            'List comments', 'View comment', 'Create comment',
            'List activities', 'View activity',
            'List discussions', 'View discussion', 'Create discussion',
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }

    /**
     * Data Analyst — Analytics & reporting
     */
    private function createDataAnalystRole()
    {
        $role = Role::firstOrCreate(['name' => 'Data Analyst']);
        $permissions = [
            'List projects', 'View project',
            'List tickets', 'View ticket', 'Create ticket',
            'View ticket status', 'View ticket priority', 'View ticket type',
            'List comments', 'View comment', 'Create comment',
            'List activities', 'View activity',
            'List timesheet data', 'View timesheet dashboard',
            'List customer feedbacks', 'View customer feedback',
            'List discussions', 'View discussion', 'Create discussion',
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }

    /**
     * Operations / Admin Project
     */
    private function createOperationsRole()
    {
        $role = Role::firstOrCreate(['name' => 'Operations']);
        $permissions = [
            'List projects', 'View project',
            'List tickets', 'View ticket', 'Create ticket', 'Update ticket',
            'List ticket statuses', 'View ticket status',
            'List ticket priorities', 'View ticket priority',
            'List ticket types', 'View ticket type',
            'List sprints', 'View sprint',
            'List comments', 'View comment', 'Create comment', 'Update comment',
            'List activities', 'View activity',
            'List discussions', 'View discussion', 'Create discussion', 'Update discussion',
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
            'List timesheet data',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }

    /**
     * HR — User management only, NO project/ticket access
     */
    private function createHRRole()
    {
        $role = Role::firstOrCreate(['name' => 'HR']);
        $permissions = [
            'List users', 'View user', 'Create user', 'Update user',
            'List roles', 'View role',
            'List tickets', 'View ticket', 'Create ticket',
            'View ticket status', 'View ticket priority', 'View ticket type',
            'List comments', 'View comment', 'Create comment',
            'List discussions', 'View discussion', 'Create discussion',
            'List daily reports', 'View daily report', 'Create daily report', 'Update daily report',
            'List weekly reports', 'View weekly report', 'Create weekly report', 'Update weekly report',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }

    /**
     * Finance — Timesheet & financial data, view-only
     */
    private function createFinanceRole()
    {
        $role = Role::firstOrCreate(['name' => 'Finance']);
        $permissions = [
            'List projects', 'View project',
            'List tickets', 'View ticket', 'Create ticket',
            'View ticket status', 'View ticket priority', 'View ticket type',
            'List comments', 'View comment', 'Create comment',
            'List timesheet data', 'View timesheet dashboard',
            'List users', 'View user',
            'List discussions', 'View discussion', 'Create discussion',
            'List daily reports', 'View daily report', 'Create daily report',
            'List weekly reports', 'View weekly report',
        ];
        $role->syncPermissions(Permission::whereIn('name', $permissions)->pluck('name')->toArray());
    }
}

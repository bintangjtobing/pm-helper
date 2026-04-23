<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\DailyReport;
use App\Models\User;

class GetDailyReportTool implements Tool
{
    public function name(): string
    {
        return 'get_daily_report';
    }

    public function description(): string
    {
        return 'Get full details of one daily report (accomplishments, plans, blockers). Own reports or reports in projects you have access to.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['id'],
            'properties' => [
                'id' => ['type' => 'integer'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $report = DailyReport::with(['user:id,name,email', 'project:id,name', 'acknowledgedByUser:id,name'])
            ->find((int) ($args['id'] ?? 0));

        if (! $report) {
            throw new \RuntimeException('Daily report not found.');
        }

        $isOwn = $report->user_id === $user->id;
        $project = $report->project;
        $hasProjectAccess = $project && ($project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists());

        if (! $isOwn && ! $hasProjectAccess) {
            throw new \RuntimeException('Access denied to this daily report.');
        }

        return [
            'id' => $report->id,
            'report_date' => optional($report->report_date)->toDateString(),
            'status' => $report->status,
            'accomplished' => $report->accomplished,
            'plans' => $report->plans,
            'blockers' => $report->blockers,
            'user' => $report->user ? ['id' => $report->user->id, 'name' => $report->user->name, 'email' => $report->user->email] : null,
            'project' => $project ? ['id' => $project->id, 'name' => $project->name] : null,
            'acknowledged_by' => $report->acknowledgedByUser ? ['id' => $report->acknowledgedByUser->id, 'name' => $report->acknowledgedByUser->name] : null,
            'acknowledged_at' => optional($report->acknowledged_at)->toIso8601String(),
            'submitted_at' => optional($report->submitted_at)->toIso8601String(),
            'created_at' => optional($report->created_at)->toIso8601String(),
        ];
    }
}

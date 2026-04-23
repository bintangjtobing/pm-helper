<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;

class CreateDailyReportTool implements Tool
{
    public function name(): string
    {
        return 'create_daily_report';
    }

    public function description(): string
    {
        return 'Create a daily report for the current user on the given project + date. Status defaults to "draft"; pass status="submitted" to submit immediately. One report per (user, project, date) — will error if a duplicate exists.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['project_id', 'report_date'],
            'properties' => [
                'project_id' => ['type' => 'integer'],
                'report_date' => ['type' => 'string', 'description' => 'ISO date, e.g. "2026-04-23".'],
                'accomplished' => ['type' => 'string'],
                'plans' => ['type' => 'string'],
                'blockers' => ['type' => 'string'],
                'status' => ['type' => 'string', 'description' => 'draft or submitted. Defaults to draft.'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $project = Project::find((int) ($args['project_id'] ?? 0));
        if (! $project) {
            throw new \RuntimeException('Project not found.');
        }
        $hasAccess = $project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists();
        if (! $hasAccess) {
            throw new \RuntimeException('Access denied to this project.');
        }

        $date = trim((string) ($args['report_date'] ?? ''));
        if ($date === '') {
            throw new \RuntimeException('report_date is required.');
        }

        $existing = DailyReport::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->whereDate('report_date', $date)
            ->first();
        if ($existing) {
            throw new \RuntimeException("A daily report already exists for this date (id {$existing->id}). Use update tool once available, or pick a different date.");
        }

        $status = $args['status'] ?? 'draft';
        if (! in_array($status, ['draft', 'submitted'], true)) {
            throw new \RuntimeException('status must be "draft" or "submitted".');
        }

        $report = DailyReport::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'report_date' => $date,
            'accomplished' => $args['accomplished'] ?? null,
            'plans' => $args['plans'] ?? null,
            'blockers' => $args['blockers'] ?? null,
            'status' => $status,
            'submitted_at' => $status === 'submitted' ? now() : null,
        ]);

        return [
            'id' => $report->id,
            'report_date' => optional($report->report_date)->toDateString(),
            'status' => $report->status,
            'project' => ['id' => $project->id, 'name' => $project->name],
            'message' => 'Daily report created.',
        ];
    }
}

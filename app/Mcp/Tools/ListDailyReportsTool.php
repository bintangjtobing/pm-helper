<?php

namespace App\Mcp\Tools;

use App\Mcp\Tool;
use App\Models\DailyReport;
use App\Models\User;

class ListDailyReportsTool implements Tool
{
    public function name(): string
    {
        return 'list_daily_reports';
    }

    public function description(): string
    {
        return 'List daily reports. Defaults to the current user. Set "user_id" to fetch reports by a specific user (only allowed when you have access to their projects), or set "all_accessible" to see reports from every project you can access.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'user_id' => ['type' => 'integer'],
                'all_accessible' => ['type' => 'boolean', 'description' => 'Fetch reports across all projects the current user has access to.'],
                'project_id' => ['type' => 'integer'],
                'from' => ['type' => 'string', 'description' => 'ISO date (inclusive).'],
                'to' => ['type' => 'string', 'description' => 'ISO date (inclusive).'],
                'status' => ['type' => 'string', 'description' => 'draft or submitted.'],
                'limit' => ['type' => 'integer', 'description' => 'Max results (default 25, max 100).'],
            ],
            'additionalProperties' => false,
        ];
    }

    public function execute(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 25), 1), 100);

        $query = DailyReport::query()
            ->with(['user:id,name', 'project:id,name']);

        if (! empty($args['all_accessible'])) {
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('users', fn ($inner) => $inner->where('users.id', $user->id));
            });
        } elseif (! empty($args['user_id'])) {
            $query->where('user_id', (int) $args['user_id']);
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('users', fn ($inner) => $inner->where('users.id', $user->id));
            });
        } else {
            $query->where('user_id', $user->id);
        }

        if (! empty($args['project_id'])) {
            $query->where('project_id', (int) $args['project_id']);
        }
        if (! empty($args['from'])) {
            $query->whereDate('report_date', '>=', $args['from']);
        }
        if (! empty($args['to'])) {
            $query->whereDate('report_date', '<=', $args['to']);
        }
        if (! empty($args['status'])) {
            $query->where('status', (string) $args['status']);
        }

        $reports = $query->orderByDesc('report_date')->limit($limit)->get();

        return [
            'count' => $reports->count(),
            'daily_reports' => $reports->map(fn ($r) => [
                'id' => $r->id,
                'report_date' => optional($r->report_date)->toDateString(),
                'status' => $r->status,
                'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name] : null,
                'project' => $r->project ? ['id' => $r->project->id, 'name' => $r->project->name] : null,
                'submitted_at' => optional($r->submitted_at)->toIso8601String(),
            ])->values(),
        ];
    }
}

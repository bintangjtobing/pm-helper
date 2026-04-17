<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\TicketActivity;
use App\Models\TicketComment;
use App\Models\WeeklyReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Unified activity feed for the mobile home dashboard.
 *
 * Pulls recent events across the projects the current user owns or is a
 * member of — status moves, comments, and submitted reports — and merges
 * them into a single chronological list.
 */
class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min((int) $request->get('limit', 25), 50);

        // Projects visible to this user
        $projectIds = Project::query()
            ->where('owner_id', $user->id)
            ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
            ->pluck('id')
            ->toArray();

        if (empty($projectIds)) {
            return response()->json(['data' => []]);
        }

        // Ticket status moves
        $moves = TicketActivity::query()
            ->with(['user:id,name,username,avatar_url', 'ticket:id,code,name,project_id'])
            ->whereHas('ticket', fn($q) => $q->whereIn('project_id', $projectIds))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (TicketActivity $a) {
                $old = $a->old_status_id ? optional(\App\Models\TicketStatus::withTrashed()->find($a->old_status_id))->name : null;
                $new = $a->new_status_id ? optional(\App\Models\TicketStatus::withTrashed()->find($a->new_status_id))->name : null;
                return [
                    'kind' => 'ticket.status_changed',
                    'id' => 'ta-' . $a->id,
                    'at' => $a->created_at?->toIso8601String(),
                    'actor' => $a->user ? [
                        'id' => $a->user->id,
                        'name' => $a->user->name,
                        'avatar_url' => $a->user->avatar_url,
                    ] : null,
                    'summary' => $old && $new ? "moved from {$old} → {$new}" : ($new ? "set status to {$new}" : 'updated status'),
                    'target' => $a->ticket ? [
                        'type' => 'ticket',
                        'id' => $a->ticket->id,
                        'code' => $a->ticket->code,
                        'title' => $a->ticket->name,
                    ] : null,
                ];
            });

        // Ticket comments
        $comments = TicketComment::query()
            ->with(['user:id,name,username,avatar_url', 'ticket:id,code,name,project_id'])
            ->whereHas('ticket', fn($q) => $q->whereIn('project_id', $projectIds))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (TicketComment $c) {
                return [
                    'kind' => 'ticket.commented',
                    'id' => 'tc-' . $c->id,
                    'at' => $c->created_at?->toIso8601String(),
                    'actor' => $c->user ? [
                        'id' => $c->user->id,
                        'name' => $c->user->name,
                        'avatar_url' => $c->user->avatar_url,
                    ] : null,
                    'summary' => 'left a comment',
                    'excerpt' => \Illuminate\Support\Str::limit(strip_tags($c->content), 140),
                    'target' => $c->ticket ? [
                        'type' => 'ticket',
                        'id' => $c->ticket->id,
                        'code' => $c->ticket->code,
                        'title' => $c->ticket->name,
                    ] : null,
                ];
            });

        // Daily reports (submitted only — drafts are private noise)
        $dailies = DailyReport::query()
            ->with('user:id,name,username,avatar_url', 'project:id,name')
            ->whereIn('project_id', $projectIds)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->limit($limit)
            ->get()
            ->map(fn(DailyReport $r) => [
                'kind' => 'daily_report.submitted',
                'id' => 'dr-' . $r->id,
                'at' => $r->submitted_at?->toIso8601String(),
                'actor' => $r->user ? [
                    'id' => $r->user->id,
                    'name' => $r->user->name,
                    'avatar_url' => $r->user->avatar_url,
                ] : null,
                'summary' => 'submitted a daily report',
                'target' => [
                    'type' => 'daily_report',
                    'id' => $r->id,
                    'title' => $r->report_date?->format('M j, Y') . ($r->project ? ' · ' . $r->project->name : ''),
                ],
            ]);

        // Weekly reports
        $weeklies = WeeklyReport::query()
            ->with('user:id,name,username,avatar_url', 'project:id,name')
            ->whereIn('project_id', $projectIds)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->limit($limit)
            ->get()
            ->map(fn(WeeklyReport $r) => [
                'kind' => 'weekly_report.submitted',
                'id' => 'wr-' . $r->id,
                'at' => $r->submitted_at?->toIso8601String(),
                'actor' => $r->user ? [
                    'id' => $r->user->id,
                    'name' => $r->user->name,
                    'avatar_url' => $r->user->avatar_url,
                ] : null,
                'summary' => 'submitted a weekly summary',
                'target' => [
                    'type' => 'weekly_report',
                    'id' => $r->id,
                    'title' => ($r->week_start?->format('M j') . ' – ' . $r->week_end?->format('M j, Y'))
                        . ($r->project ? ' · ' . $r->project->name : ''),
                ],
            ]);

        // Merge, sort by timestamp, cap
        $feed = $moves
            ->concat($comments)
            ->concat($dailies)
            ->concat($weeklies)
            ->sortByDesc('at')
            ->take($limit)
            ->values();

        return response()->json(['data' => $feed]);
    }
}

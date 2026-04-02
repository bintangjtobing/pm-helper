<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketHour;
use App\Models\User;
use Carbon\Carbon;

class WeeklyReportService
{
    public function getWeekBounds(?Carbon $date = null): array
    {
        $date = $date ?? now();

        return [
            'week_start' => $date->copy()->startOfWeek(Carbon::MONDAY),
            'week_end' => $date->copy()->endOfWeek(Carbon::SUNDAY),
        ];
    }

    public function generateAutoSummary(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $projectIds = $this->getUserProjectIds($user);

        // Snapshot: ALL tickets in user's projects (current state)
        $allTickets = $this->getAllProjectTickets($projectIds);

        // Activity: tickets specifically updated THIS week
        $ticketsUpdated = $this->getTicketsUpdated($user, $weekStart, $weekEnd);
        $ticketsCompleted = $this->getTicketsCompleted($user, $weekStart, $weekEnd);
        $statusChanges = $this->getStatusChanges($user, $weekStart, $weekEnd);
        $hoursLogged = $this->getHoursLogged($user, $weekStart, $weekEnd);

        // Build breakdowns from ALL project tickets (snapshot)
        $statusBreakdown = [];
        $typeBreakdown = [];
        $priorityBreakdown = [];
        $projectBreakdown = [];

        foreach ($allTickets as $ticket) {
            $status = $ticket['status'];
            $statusBreakdown[$status] = ($statusBreakdown[$status] ?? 0) + 1;

            $type = $ticket['type'] ?? 'Other';
            $typeBreakdown[$type] = ($typeBreakdown[$type] ?? 0) + 1;

            $priority = $ticket['priority'] ?? 'N/A';
            $priorityBreakdown[$priority] = ($priorityBreakdown[$priority] ?? 0) + 1;

            $project = $ticket['project_name'];
            if (!isset($projectBreakdown[$project])) {
                $projectBreakdown[$project] = ['total' => 0, 'tickets' => []];
            }
            $projectBreakdown[$project]['total']++;
            $projectBreakdown[$project]['tickets'][] = $ticket;
        }

        $totalAll = count($allTickets);
        $totalUpdated = count($ticketsUpdated);
        $totalCompleted = count($ticketsCompleted);

        // Completion rate based on "done" statuses across all tickets
        $doneStatuses = ['QA Passed', 'Done', 'Completed', 'Closed', 'Resolved'];
        $completedCount = 0;
        foreach ($allTickets as $t) {
            if (in_array($t['status'], $doneStatuses)) {
                $completedCount++;
            }
        }
        $completionRate = $totalAll > 0 ? round(($completedCount / $totalAll) * 100, 1) : 0;

        return [
            'tickets_updated' => $allTickets, // Full snapshot
            'tickets_changed_this_week' => $ticketsUpdated, // Only changed this week
            'tickets_completed' => $ticketsCompleted,
            'status_changes' => $statusChanges,
            'hours_logged' => $hoursLogged,
            'progress_summary' => [
                'total_tickets_touched' => $totalAll,
                'tickets_updated_this_week' => $totalUpdated,
                'tickets_completed' => $completedCount,
                'completion_rate' => $completionRate,
                'status_changes_count' => count($statusChanges),
                'total_hours' => $hoursLogged['total_hours'] ?? 0,
                'projects_worked' => count($projectBreakdown),
            ],
            'status_breakdown' => $statusBreakdown,
            'project_breakdown' => $projectBreakdown,
            'type_breakdown' => $typeBreakdown,
            'priority_breakdown' => $priorityBreakdown,
        ];
    }

    public function formatSummaryAsMarkdown(array $autoSummary): string
    {
        $md = '';
        $progress = $autoSummary['progress_summary'] ?? [];

        // Executive Summary
        if (!empty($progress) && $progress['total_tickets_touched'] > 0) {
            $md .= '<h2>Weekly Progress Summary</h2>';
            $md .= '<table><thead><tr><th>Metric</th><th>Value</th></tr></thead><tbody>';
            $md .= '<tr><td>Total Tickets Touched</td><td>' . $progress['total_tickets_touched'] . '</td></tr>';
            $md .= '<tr><td>Tickets Completed</td><td>' . $progress['tickets_completed'] . '</td></tr>';
            $md .= '<tr><td>Completion Rate</td><td>' . $progress['completion_rate'] . '%</td></tr>';
            $md .= '<tr><td>Status Changes</td><td>' . $progress['status_changes_count'] . '</td></tr>';
            $md .= '<tr><td>Hours Logged</td><td>' . $progress['total_hours'] . 'h</td></tr>';
            $md .= '<tr><td>Projects Worked</td><td>' . $progress['projects_worked'] . '</td></tr>';
            $md .= '</tbody></table>';
        }

        // Tickets Completed
        $completed = $autoSummary['tickets_completed'] ?? [];
        if (!empty($completed)) {
            $md .= "<h3>Tickets Completed (" . count($completed) . ")</h3><ul>";
            foreach (array_slice($completed, 0, 50) as $ticket) {
                $md .= "<li><strong>{$ticket['code']}</strong> — {$ticket['name']} ({$ticket['project_name']})</li>";
            }
            $md .= '</ul>';
        }

        // Tickets Updated
        $updated = $autoSummary['tickets_updated'] ?? [];
        if (!empty($updated)) {
            $md .= "<h3>Tickets Updated (" . count($updated) . ")</h3><ul>";
            foreach (array_slice($updated, 0, 50) as $ticket) {
                $md .= "<li><strong>{$ticket['code']}</strong> — {$ticket['name']} [{$ticket['status']}]</li>";
            }
            $md .= '</ul>';
        }

        // Hours Logged
        $hours = $autoSummary['hours_logged'] ?? [];
        if (!empty($hours['entries'])) {
            $total = $hours['total_hours'] ?? 0;
            $md .= "<h3>Time Logged ({$total}h total)</h3><ul>";
            foreach (array_slice($hours['entries'], 0, 50) as $entry) {
                $md .= "<li><strong>{$entry['ticket_code']}</strong> — {$entry['hours']}h";
                if (!empty($entry['activity'])) {
                    $md .= " ({$entry['activity']})";
                }
                $md .= '</li>';
            }
            $md .= '</ul>';
        }

        if (empty($md)) {
            $md = '<p><em>No activity recorded this week. Add your notes below.</em></p>';
        }

        return $md;
    }

    /**
     * Get IDs of projects the user has access to.
     */
    private function getUserProjectIds(User $user): array
    {
        $owned = \App\Models\Project::where('owner_id', $user->id)->pluck('id');
        $member = $user->projects()->pluck('projects.id');
        return $owned->merge($member)->unique()->toArray();
    }

    private function getAllProjectTickets(array $projectIds): array
    {
        return Ticket::whereIn('project_id', $projectIds)
            ->with(['project', 'status', 'type', 'priority'])
            ->orderBy('code')
            ->get()
            ->map(fn ($ticket) => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'name' => $ticket->name,
                'project_name' => $ticket->project?->name ?? 'N/A',
                'status' => $ticket->status?->name ?? 'N/A',
                'status_color' => $ticket->status?->color ?? '#6b7280',
                'type' => $ticket->type?->name ?? 'N/A',
                'priority' => $ticket->priority?->name ?? 'N/A',
                'priority_color' => $ticket->priority?->color ?? '#6b7280',
            ])
            ->toArray();
    }

    private function getTicketsUpdated(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $projectIds = $this->getUserProjectIds($user);

        return Ticket::where(function ($q) use ($user, $projectIds) {
                $q->where('owner_id', $user->id)
                  ->orWhere('responsible_id', $user->id)
                  ->orWhereIn('project_id', $projectIds);
            })
            ->whereBetween('updated_at', [$weekStart, $weekEnd])
            ->with(['project', 'status', 'type', 'priority'])
            ->limit(200)
            ->get()
            ->map(fn ($ticket) => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'name' => $ticket->name,
                'project_name' => $ticket->project?->name ?? 'N/A',
                'status' => $ticket->status?->name ?? 'N/A',
                'status_color' => $ticket->status?->color ?? '#6b7280',
                'type' => $ticket->type?->name ?? 'N/A',
                'priority' => $ticket->priority?->name ?? 'N/A',
                'priority_color' => $ticket->priority?->color ?? '#6b7280',
            ])
            ->toArray();
    }

    private function getTicketsCompleted(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $projectIds = $this->getUserProjectIds($user);

        return Ticket::where(function ($q) use ($user, $projectIds) {
                $q->where('owner_id', $user->id)
                  ->orWhere('responsible_id', $user->id)
                  ->orWhereIn('project_id', $projectIds);
            })
            ->completedBetween($weekStart, $weekEnd)
            ->with(['project', 'status'])
            ->limit(200)
            ->get()
            ->map(fn ($ticket) => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'name' => $ticket->name,
                'project_name' => $ticket->project?->name ?? 'N/A',
            ])
            ->toArray();
    }

    private function getStatusChanges(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $projectIds = $this->getUserProjectIds($user);

        return TicketActivity::where(function ($q) use ($user, $projectIds) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('ticket', fn($tq) => $tq->whereIn('project_id', $projectIds));
            })
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->with(['ticket', 'oldStatus', 'newStatus'])
            ->validStatuses()
            ->limit(200)
            ->get()
            ->map(fn ($activity) => [
                'ticket_code' => $activity->ticket?->code ?? 'N/A',
                'ticket_name' => $activity->ticket?->name ?? 'N/A',
                'from_status' => $activity->oldStatus?->name ?? 'N/A',
                'to_status' => $activity->newStatus?->name ?? 'N/A',
                'changed_at' => $activity->created_at->format('Y-m-d H:i'),
            ])
            ->toArray();
    }

    private function getHoursLogged(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        $entries = TicketHour::where('user_id', $user->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->with(['ticket', 'activity'])
            ->limit(100)
            ->get();

        return [
            'total_hours' => round($entries->sum('value'), 2),
            'entries' => $entries->map(fn ($entry) => [
                'ticket_id' => $entry->ticket?->id,
                'ticket_code' => $entry->ticket?->code ?? 'N/A',
                'ticket_name' => $entry->ticket?->name ?? 'N/A',
                'hours' => round($entry->value, 2),
                'activity' => $entry->activity?->name ?? '',
                'comment' => $entry->comment ?? '',
            ])->toArray(),
        ];
    }
}

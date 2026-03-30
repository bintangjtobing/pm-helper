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
        return [
            'tickets_updated' => $this->getTicketsUpdated($user, $weekStart, $weekEnd),
            'tickets_completed' => $this->getTicketsCompleted($user, $weekStart, $weekEnd),
            'status_changes' => $this->getStatusChanges($user, $weekStart, $weekEnd),
            'hours_logged' => $this->getHoursLogged($user, $weekStart, $weekEnd),
        ];
    }

    public function formatSummaryAsMarkdown(array $autoSummary): string
    {
        $md = '';

        // Tickets Completed
        $completed = $autoSummary['tickets_completed'] ?? [];
        if (!empty($completed)) {
            $md .= "<h3>Tickets Completed ({$this->count($completed)})</h3><ul>";
            foreach (array_slice($completed, 0, 50) as $ticket) {
                $md .= "<li><strong>{$ticket['code']}</strong> — {$ticket['name']} ({$ticket['project_name']})</li>";
            }
            $md .= '</ul>';
        }

        // Tickets Updated
        $updated = $autoSummary['tickets_updated'] ?? [];
        if (!empty($updated)) {
            $md .= "<h3>Tickets Updated ({$this->count($updated)})</h3><ul>";
            foreach (array_slice($updated, 0, 50) as $ticket) {
                $md .= "<li><strong>{$ticket['code']}</strong> — {$ticket['name']} [{$ticket['status']}]</li>";
            }
            $md .= '</ul>';
        }

        // Status Changes
        $changes = $autoSummary['status_changes'] ?? [];
        if (!empty($changes)) {
            $md .= "<h3>Status Changes ({$this->count($changes)})</h3><ul>";
            foreach (array_slice($changes, 0, 50) as $change) {
                $md .= "<li><strong>{$change['ticket_code']}</strong> — {$change['from_status']} → {$change['to_status']}</li>";
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

    private function getTicketsUpdated(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        return Ticket::where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhere('responsible_id', $user->id);
            })
            ->whereBetween('updated_at', [$weekStart, $weekEnd])
            ->with(['project', 'status'])
            ->limit(100)
            ->get()
            ->map(fn ($ticket) => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'name' => $ticket->name,
                'project_name' => $ticket->project?->name ?? 'N/A',
                'status' => $ticket->status?->name ?? 'N/A',
            ])
            ->toArray();
    }

    private function getTicketsCompleted(User $user, Carbon $weekStart, Carbon $weekEnd): array
    {
        return Ticket::where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhere('responsible_id', $user->id);
            })
            ->completedBetween($weekStart, $weekEnd)
            ->with(['project', 'status'])
            ->limit(100)
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
        return TicketActivity::where('user_id', $user->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->with(['ticket', 'oldStatus', 'newStatus'])
            ->validStatuses()
            ->limit(100)
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
                'ticket_code' => $entry->ticket?->code ?? 'N/A',
                'ticket_name' => $entry->ticket?->name ?? 'N/A',
                'hours' => round($entry->value, 2),
                'activity' => $entry->activity?->name ?? '',
                'comment' => $entry->comment ?? '',
            ])->toArray(),
        ];
    }

    private function count(array $items): int
    {
        return count($items);
    }
}

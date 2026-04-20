<?php

namespace App\Services\Goals\Adapters;

use App\Models\GoalPeriod;
use App\Models\KeyResult;
use App\Models\Ticket;

class TicketsAdapter implements ProgressAdapter
{
    public function label(): string
    {
        return 'Tickets';
    }

    /**
     * Formula schema:
     * {
     *   "filter": {
     *     "assignee": "owner" | "any",  // owner = responsible_id = goal owner
     *     "status_ids": [int, ...],      // optional; otherwise all statuses
     *     "project_id": int              // optional
     *   },
     *   "aggregate": "count"              // only count for now
     * }
     */
    public function compute(KeyResult $kr, array $formula, GoalPeriod $period, ?int $ownerId): float
    {
        $filter = $formula['filter'] ?? [];
        $aggregate = $formula['aggregate'] ?? 'count';

        $query = Ticket::query()
            ->whereBetween('updated_at', [$period->start_date->startOfDay(), $period->end_date->endOfDay()]);

        $assignee = $filter['assignee'] ?? 'owner';
        if ($assignee === 'owner' && $ownerId) {
            $query->where('responsible_id', $ownerId);
        }

        if (! empty($filter['status_ids']) && is_array($filter['status_ids'])) {
            $query->whereIn('status_id', $filter['status_ids']);
        }

        if (! empty($filter['project_id'])) {
            $query->where('project_id', (int) $filter['project_id']);
        }

        return match ($aggregate) {
            'count' => (float) $query->count(),
            default => 0.0,
        };
    }
}

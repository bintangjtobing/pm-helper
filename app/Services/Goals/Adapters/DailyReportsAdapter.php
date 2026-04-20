<?php

namespace App\Services\Goals\Adapters;

use App\Models\DailyReport;
use App\Models\GoalPeriod;
use App\Models\KeyResult;

class DailyReportsAdapter implements ProgressAdapter
{
    public function label(): string
    {
        return 'Daily Reports';
    }

    /**
     * Formula schema:
     * {
     *   "filter": {
     *     "user": "owner" | "any",
     *     "status": "submitted" | "acknowledged" | "any"
     *   },
     *   "aggregate": "count"
     * }
     */
    public function compute(KeyResult $kr, array $formula, GoalPeriod $period, ?int $ownerId): float
    {
        $filter = $formula['filter'] ?? [];
        $aggregate = $formula['aggregate'] ?? 'count';

        $query = DailyReport::query()
            ->whereBetween('report_date', [$period->start_date, $period->end_date]);

        if (($filter['user'] ?? 'owner') === 'owner' && $ownerId) {
            $query->where('user_id', $ownerId);
        }

        $status = $filter['status'] ?? 'submitted';
        if ($status !== 'any') {
            $query->where('status', $status);
        }

        return match ($aggregate) {
            'count' => (float) $query->count(),
            default => 0.0,
        };
    }
}

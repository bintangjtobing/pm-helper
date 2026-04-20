<?php

namespace App\Services\Goals\Adapters;

use App\Models\GoalPeriod;
use App\Models\KeyResult;
use App\Models\WeeklyReport;

class WeeklyReportsAdapter implements ProgressAdapter
{
    public function label(): string
    {
        return 'Weekly Reports';
    }

    /**
     * Formula schema identical to DailyReportsAdapter.
     */
    public function compute(KeyResult $kr, array $formula, GoalPeriod $period, ?int $ownerId): float
    {
        $filter = $formula['filter'] ?? [];
        $aggregate = $formula['aggregate'] ?? 'count';

        $query = WeeklyReport::query()
            ->whereBetween('week_start', [$period->start_date, $period->end_date]);

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

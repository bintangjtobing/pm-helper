<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\KeyResult;

class GoalWeightValidator
{
    public const TOLERANCE = 0.01;

    /**
     * Sum of Objective weights per user per period. Returns float total (0–100).
     * Optional $excludeGoalId lets the caller pretend the goal being edited isn't yet saved.
     */
    public static function userObjectivesTotal(int $userId, int $periodId, ?int $excludeGoalId = null): float
    {
        $query = Goal::query()
            ->where('owner_id', $userId)
            ->where('period_id', $periodId)
            ->where('type', 'objective')
            ->whereNotIn('status', ['cancelled']);

        if ($excludeGoalId) {
            $query->where('id', '!=', $excludeGoalId);
        }

        return (float) $query->sum('weight');
    }

    /**
     * Sum of KR weights within a single Goal. Returns float total (0–100).
     */
    public static function goalKeyResultsTotal(int $goalId, ?int $excludeKrId = null): float
    {
        $query = KeyResult::query()->where('goal_id', $goalId);

        if ($excludeKrId) {
            $query->where('id', '!=', $excludeKrId);
        }

        return (float) $query->sum('weight');
    }

    public static function isValidTotal(float $total): bool
    {
        return abs($total - 100.0) <= self::TOLERANCE;
    }

    /**
     * Returns a human-readable label for a weight total (for live indicators).
     */
    public static function label(float $total): string
    {
        $remaining = 100.0 - $total;

        if (self::isValidTotal($total)) {
            return "Total: {$total}% — OK";
        }

        if ($total < 100.0) {
            return "Total: {$total}% — " . round($remaining, 2) . '% remaining';
        }

        return "Total: {$total}% — exceeds by " . round(-$remaining, 2) . '%';
    }
}

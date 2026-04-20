<?php

namespace App\Services\Goals;

use App\Models\KeyResult;
use App\Models\KeyResultUpdate;
use App\Services\Goals\Adapters\DailyReportsAdapter;
use App\Services\Goals\Adapters\ProgressAdapter;
use App\Services\Goals\Adapters\TicketsAdapter;
use App\Services\Goals\Adapters\WeeklyReportsAdapter;

class GoalProgressCalculator
{
    /**
     * Registry of auto_source => adapter instance.
     */
    public static function adapters(): array
    {
        return [
            'tickets' => new TicketsAdapter(),
            'daily_reports' => new DailyReportsAdapter(),
            'weekly_reports' => new WeeklyReportsAdapter(),
        ];
    }

    public static function adapter(string $source): ?ProgressAdapter
    {
        return self::adapters()[$source] ?? null;
    }

    /**
     * Recalculate a single KR. Returns the KeyResultUpdate if the value
     * changed, null otherwise (or null if the KR isn't auto/hybrid).
     */
    public function recalculate(KeyResult $kr): ?KeyResultUpdate
    {
        if (! $kr->isAuto()) {
            return null;
        }

        $source = $kr->auto_source;
        $adapter = self::adapter($source);
        if (! $adapter) {
            return null;
        }

        $goal = $kr->goal;
        if (! $goal) {
            return null;
        }

        $period = $goal->period;
        if (! $period) {
            return null;
        }

        $formula = is_array($kr->auto_formula) ? $kr->auto_formula : [];
        $value = $adapter->compute($kr, $formula, $period, $goal->owner_id);

        // No-op if value unchanged (avoid spam in history).
        if ((float) $kr->current_value === $value) {
            return null;
        }

        return $kr->recordUpdate(
            value: $value,
            source: 'auto',
            userId: null,
            note: "Auto-calculated from {$adapter->label()}",
        );
    }

    /**
     * Recalculate all auto/hybrid KRs under active periods.
     * Returns the number of KRs that were updated.
     */
    public function recalculateAll(): int
    {
        $updated = 0;

        KeyResult::query()
            ->whereIn('progress_mode', ['auto', 'hybrid'])
            ->whereHas('goal.period', fn ($q) => $q->where('status', 'active'))
            ->with('goal.period')
            ->chunkById(100, function ($krs) use (&$updated) {
                foreach ($krs as $kr) {
                    if ($this->recalculate($kr)) {
                        $updated++;
                    }
                }
            });

        return $updated;
    }
}

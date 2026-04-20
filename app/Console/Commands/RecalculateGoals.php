<?php

namespace App\Console\Commands;

use App\Models\KeyResult;
use App\Services\Goals\GoalProgressCalculator;
use Illuminate\Console\Command;

class RecalculateGoals extends Command
{
    protected $signature = 'goals:recalculate
                            {--kr= : Specific key result ID to recalc}
                            {--dry-run : Compute but do not persist changes}';

    protected $description = 'Recalculate current_value for all auto/hybrid Key Results in active periods';

    public function handle(GoalProgressCalculator $calculator): int
    {
        $dryRun = $this->option('dry-run');
        $krId = $this->option('kr');

        if ($krId) {
            $kr = KeyResult::find($krId);
            if (! $kr) {
                $this->error("KR #{$krId} not found.");
                return self::FAILURE;
            }

            if ($dryRun) {
                $this->warn("Dry-run mode — no changes written.");
                $adapter = GoalProgressCalculator::adapter((string) $kr->auto_source);
                if (! $adapter) {
                    $this->line("KR #{$kr->id} has no valid adapter (progress_mode={$kr->progress_mode}, auto_source={$kr->auto_source}).");
                    return self::SUCCESS;
                }
                $value = $adapter->compute(
                    $kr,
                    is_array($kr->auto_formula) ? $kr->auto_formula : [],
                    $kr->goal->period,
                    $kr->goal->owner_id,
                );
                $this->info("Would set KR #{$kr->id} current_value: {$kr->current_value} → {$value}");
                return self::SUCCESS;
            }

            $update = $calculator->recalculate($kr);
            $this->info($update
                ? "KR #{$kr->id} updated: {$update->previous_value} → {$update->value}"
                : "KR #{$kr->id} unchanged.");
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('Dry-run not supported for bulk mode. Use --kr= for a specific KR.');
            return self::FAILURE;
        }

        $count = $calculator->recalculateAll();
        $this->info("Recalculated {$count} Key Results with changed values.");

        return self::SUCCESS;
    }
}

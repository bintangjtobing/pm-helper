<?php

namespace App\Services\Goals\Adapters;

use App\Models\GoalPeriod;
use App\Models\KeyResult;

interface ProgressAdapter
{
    /**
     * Compute the current_value for a KR from an auto_formula JSON config.
     * Returns a float (which may be 0).
     *
     * $formula is the full auto_formula array (already decoded from JSON).
     * $ownerId defaults to the parent Goal's owner_id.
     */
    public function compute(KeyResult $kr, array $formula, GoalPeriod $period, ?int $ownerId): float;

    /**
     * Human-readable label for UI dropdowns.
     */
    public function label(): string;
}

<?php

namespace App\Rules;

use App\Services\GoalWeightValidator;
use Illuminate\Contracts\Validation\Rule;

/**
 * Ensures adding this Objective's weight to the owner's other Objectives
 * in the same period does not exceed 100%.
 *
 * Does NOT require sum == 100 at save time (drafts are allowed to be
 * under-allocated); that check is enforced at "activate" time.
 */
class ObjectiveWeightFits implements Rule
{
    protected string $message = 'Weight exceeds 100% for this user in this period.';

    public function __construct(
        protected int $userId,
        protected int $periodId,
        protected ?int $excludeGoalId = null,
    ) {
    }

    public function passes($attribute, $value): bool
    {
        $other = GoalWeightValidator::userObjectivesTotal(
            $this->userId,
            $this->periodId,
            $this->excludeGoalId,
        );

        $total = $other + (float) $value;

        if ($total > 100.0 + GoalWeightValidator::TOLERANCE) {
            $remaining = max(0, 100.0 - $other);
            $this->message = "Only {$remaining}% weight budget left for this user in this period (already allocated: {$other}%).";
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->message;
    }
}

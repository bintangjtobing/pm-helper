<?php

namespace App\Rules;

use App\Services\GoalWeightValidator;
use Illuminate\Contracts\Validation\Rule;

/**
 * Ensures adding this KR's weight to the sibling KRs under the same Goal
 * does not exceed 100%.
 */
class KeyResultWeightFits implements Rule
{
    protected string $message = 'KR weight exceeds 100% for this objective.';

    public function __construct(
        protected int $goalId,
        protected ?int $excludeKrId = null,
    ) {
    }

    public function passes($attribute, $value): bool
    {
        $other = GoalWeightValidator::goalKeyResultsTotal($this->goalId, $this->excludeKrId);
        $total = $other + (float) $value;

        if ($total > 100.0 + GoalWeightValidator::TOLERANCE) {
            $remaining = max(0, 100.0 - $other);
            $this->message = "Only {$remaining}% KR weight budget left under this objective (already allocated: {$other}%).";
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->message;
    }
}

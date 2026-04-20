<?php

namespace App\Services\Goals;

use App\Models\Goal;
use App\Models\GoalPeriod;
use App\Models\GoalReview;
use App\Models\KeyResult;
use App\Models\KeyResultReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GoalReviewGenerator
{
    /**
     * Create goal_reviews (and nested key_result_reviews) for every user who
     * owns at least one individual Objective in the given period.
     *
     * Idempotent: if a review already exists for (period, user), it's left
     * alone. New KR reviews are appended for any KRs that weren't snapshotted
     * before.
     *
     * Returns the number of goal_reviews created (not updated).
     */
    public function generate(GoalPeriod $period): int
    {
        $userIds = Goal::query()
            ->where('period_id', $period->id)
            ->where('level', 'individual')
            ->where('type', 'objective')
            ->whereNotIn('status', ['cancelled'])
            ->whereNotNull('owner_id')
            ->pluck('owner_id')
            ->unique()
            ->values();

        $created = 0;

        DB::transaction(function () use ($userIds, $period, &$created) {
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if (! $user) {
                    continue;
                }

                $review = GoalReview::firstOrNew([
                    'period_id' => $period->id,
                    'user_id' => $userId,
                ]);

                $isNew = ! $review->exists;

                if ($isNew) {
                    $review->supervisor_id = $user->supervisor_id;
                    $review->status = GoalReview::STATUS_PENDING_SELF;
                }

                // Always refresh the system score from current KR state at generation time.
                $review->system_score = $this->computeSystemScore($period->id, $userId);
                $review->save();

                if ($isNew) {
                    $created++;
                }

                $this->syncKeyResultReviews($review, $period->id, $userId);
            }
        });

        return $created;
    }

    /**
     * Weighted achievement for one user in one period (0–100).
     */
    protected function computeSystemScore(int $periodId, int $userId): float
    {
        $goals = Goal::query()
            ->with('keyResults')
            ->where('period_id', $periodId)
            ->where('owner_id', $userId)
            ->where('type', 'objective')
            ->whereNotIn('status', ['cancelled'])
            ->get();

        $total = 0.0;
        foreach ($goals as $goal) {
            $total += ((float) $goal->weight / 100) * $goal->achievement;
        }

        return round(min(100, max(0, $total)), 2);
    }

    /**
     * Create a KeyResultReview snapshot for each KR under this user's Objectives
     * if one doesn't exist yet.
     */
    protected function syncKeyResultReviews(GoalReview $review, int $periodId, int $userId): void
    {
        $krs = KeyResult::query()
            ->whereHas('goal', fn ($q) => $q
                ->where('period_id', $periodId)
                ->where('owner_id', $userId)
                ->where('type', 'objective')
                ->whereNotIn('status', ['cancelled']))
            ->get();

        foreach ($krs as $kr) {
            $existing = KeyResultReview::where('goal_review_id', $review->id)
                ->where('key_result_id', $kr->id)
                ->first();

            if ($existing) {
                continue;
            }

            KeyResultReview::create([
                'goal_review_id' => $review->id,
                'key_result_id' => $kr->id,
                'snapshot_current' => $kr->current_value,
                'snapshot_target' => $kr->target_value,
                'system_score' => $kr->progress_percent,
            ]);
        }
    }
}

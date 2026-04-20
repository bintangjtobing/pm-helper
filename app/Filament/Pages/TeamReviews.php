<?php

namespace App\Filament\Pages;

use App\Models\GoalReview;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TeamReviews extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';

    protected static ?string $slug = 'team-reviews';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.team-reviews';

    public ?int $reviewId = null;

    public array $krScores = [];

    public array $krNotes = [];

    public string $feedback = '';

    public function mount(): void
    {
        // Pick the most recent pending review assigned to me.
        $pending = $this->baseReviewQuery()
            ->where('status', GoalReview::STATUS_PENDING_SUPERVISOR)
            ->orderByDesc('self_submitted_at')
            ->first();

        $this->reviewId = $pending?->id
            ?? $this->baseReviewQuery()->orderByDesc('created_at')->value('id');

        $this->hydrate();
    }

    public function updatedReviewId(): void
    {
        $this->hydrate();
    }

    protected function baseReviewQuery()
    {
        $user = auth()->user();
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');

        $query = GoalReview::query()->with('user');

        if ($isSuperAdmin) {
            return $query;
        }

        return $query->where('supervisor_id', $user->id);
    }

    protected function hydrate(): void
    {
        $this->krScores = [];
        $this->krNotes = [];
        $this->feedback = '';

        $review = $this->currentReview();
        if (! $review) {
            return;
        }

        $this->feedback = (string) ($review->supervisor_feedback ?? '');

        foreach ($review->keyResultReviews as $krReview) {
            $this->krScores[$krReview->id] = $krReview->final_score !== null
                ? (float) $krReview->final_score
                : ($krReview->self_score !== null ? (float) $krReview->self_score : (float) $krReview->system_score);
            $this->krNotes[$krReview->id] = (string) ($krReview->notes ?? '');
        }
    }

    public function currentReview(): ?GoalReview
    {
        if (! $this->reviewId) {
            return null;
        }

        return $this->baseReviewQuery()
            ->with(['user.department', 'period', 'keyResultReviews.keyResult.goal'])
            ->where('id', $this->reviewId)
            ->first();
    }

    public function submitSupervisorReview(): void
    {
        $review = $this->currentReview();
        if (! $review || ! $review->isSupervisorReviewable()) {
            Notification::make()->title('This review is not editable right now.')->danger()->send();
            return;
        }

        $totalWeighted = 0.0;
        $totalWeight = 0.0;

        foreach ($review->keyResultReviews as $krReview) {
            $score = (float) ($this->krScores[$krReview->id] ?? $krReview->system_score);
            $score = max(0, min(100, $score));
            $note = $this->krNotes[$krReview->id] ?? null;

            $krReview->update([
                'final_score' => $score,
                'notes' => $note,
            ]);

            $kr = $krReview->keyResult;
            if ($kr) {
                $krW = (float) $kr->weight;
                $goalW = (float) ($kr->goal?->weight ?? 0);
                $combined = ($krW / 100) * ($goalW / 100) * 100;
                $totalWeighted += $combined * $score / 100;
                $totalWeight += $combined;
            }
        }

        $finalOverall = $totalWeight > 0
            ? round(min(100, max(0, ($totalWeighted / $totalWeight) * 100)), 2)
            : 0;

        $review->update([
            'final_score' => $finalOverall,
            'supervisor_feedback' => $this->feedback,
            'status' => GoalReview::STATUS_COMPLETED,
            'supervisor_reviewed_at' => now(),
        ]);

        if ($review->user) {
            $review->user->notify(new \App\Notifications\GoalReviewCompleted($review));
        }

        Notification::make()
            ->title('Review completed')
            ->body('The employee has been notified.')
            ->success()
            ->send();

        $this->hydrate();
    }

    protected static function getNavigationLabel(): string
    {
        return __('Team Reviews');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Performance');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            return true;
        }

        return User::where('supervisor_id', $user->id)->exists();
    }

    public function getViewData(): array
    {
        $review = $this->currentReview();

        $reviews = $this->baseReviewQuery()
            ->with(['user', 'period'])
            ->orderByDesc('created_at')
            ->get();

        $pendingCount = $this->baseReviewQuery()
            ->where('status', GoalReview::STATUS_PENDING_SUPERVISOR)
            ->count();

        return [
            'review' => $review,
            'reviews' => $reviews,
            'pendingCount' => $pendingCount,
        ];
    }
}

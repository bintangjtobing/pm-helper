<?php

namespace App\Filament\Pages;

use App\Models\GoalReview;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MyReview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-check';

    protected static ?string $slug = 'my-review';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.my-review';

    public ?int $reviewId = null;

    public array $krScores = [];

    public string $narrative = '';

    public function mount(): void
    {
        // Pick the most recent non-completed review, else latest review.
        $pending = GoalReview::where('user_id', auth()->id())
            ->whereIn('status', [GoalReview::STATUS_PENDING_SELF, GoalReview::STATUS_PENDING_SUPERVISOR, GoalReview::STATUS_DISPUTED])
            ->orderByDesc('created_at')
            ->first();

        $this->reviewId = $pending?->id
            ?? GoalReview::where('user_id', auth()->id())->orderByDesc('created_at')->value('id');

        $this->hydrateFromReview();
    }

    public function updatedReviewId(): void
    {
        $this->hydrateFromReview();
    }

    protected function hydrateFromReview(): void
    {
        $this->krScores = [];
        $this->narrative = '';

        $review = $this->currentReview();
        if (! $review) {
            return;
        }

        $this->narrative = (string) ($review->self_narrative ?? '');

        foreach ($review->keyResultReviews as $krReview) {
            $this->krScores[$krReview->id] = $krReview->self_score !== null
                ? (float) $krReview->self_score
                : (float) $krReview->system_score;
        }
    }

    public function currentReview(): ?GoalReview
    {
        if (! $this->reviewId) {
            return null;
        }

        return GoalReview::with(['period', 'supervisor', 'keyResultReviews.keyResult.goal'])
            ->where('user_id', auth()->id())
            ->where('id', $this->reviewId)
            ->first();
    }

    public function submitSelfReview(): void
    {
        $review = $this->currentReview();
        if (! $review || ! $review->isSelfSubmittable()) {
            Notification::make()->title('This review is not editable right now.')->danger()->send();
            return;
        }

        // Persist per-KR self scores
        $totalWeighted = 0.0;
        $totalWeight = 0.0;
        foreach ($review->keyResultReviews as $krReview) {
            $score = (float) ($this->krScores[$krReview->id] ?? $krReview->system_score);
            $score = max(0, min(100, $score));
            $krReview->update(['self_score' => $score]);

            $kr = $krReview->keyResult;
            if ($kr) {
                $krW = (float) $kr->weight;
                $goalW = (float) ($kr->goal?->weight ?? 0);
                $combined = ($krW / 100) * ($goalW / 100) * 100; // contribution weight
                $totalWeighted += $combined * $score / 100;
                $totalWeight += $combined;
            }
        }

        $selfOverall = $totalWeight > 0
            ? round(min(100, max(0, ($totalWeighted / $totalWeight) * 100)), 2)
            : 0;

        $review->update([
            'self_score' => $selfOverall,
            'self_narrative' => $this->narrative,
            'status' => GoalReview::STATUS_PENDING_SUPERVISOR,
            'self_submitted_at' => now(),
        ]);

        // Notify supervisor
        if ($review->supervisor_id) {
            $review->supervisor->notify(new \App\Notifications\GoalReviewSelfSubmitted($review));
        }

        Notification::make()
            ->title('Self-review submitted')
            ->body($review->supervisor_id ? 'Your supervisor has been notified.' : 'No supervisor assigned — contact admin.')
            ->success()
            ->send();

        $this->hydrateFromReview();
    }

    public function acknowledge(): void
    {
        $review = $this->currentReview();
        if (! $review || ! $review->isCompleted()) {
            return;
        }

        $review->update(['acknowledged_at' => now()]);

        Notification::make()->title('Acknowledged. Thanks!')->success()->send();
        $this->hydrateFromReview();
    }

    public function dispute(): void
    {
        $review = $this->currentReview();
        if (! $review || ! $review->isCompleted()) {
            return;
        }

        $review->update(['status' => GoalReview::STATUS_DISPUTED]);

        if ($review->supervisor_id) {
            $review->supervisor->notify(new \App\Notifications\GoalReviewDisputed($review));
        }

        Notification::make()->title('Marked as disputed. Your supervisor has been notified.')->warning()->send();
        $this->hydrateFromReview();
    }

    protected static function getNavigationLabel(): string
    {
        return __('My Review');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Performance');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check()
            && GoalReview::where('user_id', auth()->id())->exists();
    }

    public function getViewData(): array
    {
        $review = $this->currentReview();

        $reviews = GoalReview::with('period')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return [
            'review' => $review,
            'reviews' => $reviews,
        ];
    }
}

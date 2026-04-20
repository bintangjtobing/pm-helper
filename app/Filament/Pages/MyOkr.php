<?php

namespace App\Filament\Pages;

use App\Models\Goal;
use App\Models\GoalPeriod;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class MyOkr extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $slug = 'my-okr';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.my-okr';

    public ?int $periodId = null;

    public function mount(): void
    {
        // Default to the most recent active period, else the latest period.
        $this->periodId = $this->periodId ?? GoalPeriod::active()->orderByDesc('start_date')->value('id')
            ?? GoalPeriod::orderByDesc('start_date')->value('id');
    }

    public function updatedPeriodId(): void
    {
        // Livewire auto re-renders the page on property update.
    }

    protected static function getNavigationLabel(): string
    {
        return __('My OKR');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Performance');
    }

    public function getViewData(): array
    {
        $userId = auth()->id();
        $period = $this->periodId ? GoalPeriod::find($this->periodId) : null;

        $periods = GoalPeriod::orderByDesc('start_date')->get();

        $goals = Goal::query()
            ->with(['keyResults', 'period', 'department', 'owner'])
            ->when($period, fn ($q) => $q->where('period_id', $period->id))
            ->where(function ($q) use ($userId) {
                $q->where('owner_id', $userId);
            })
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        $totalWeight = (float) $goals->sum('weight');
        $achievement = 0.0;
        foreach ($goals as $g) {
            $achievement += ((float) $g->weight / 100) * $g->achievement;
        }
        $achievement = round(min(100, max(0, $achievement)), 2);

        return [
            'periods' => $periods,
            'period' => $period,
            'goals' => $goals,
            'totalWeight' => $totalWeight,
            'overallAchievement' => $achievement,
        ];
    }
}

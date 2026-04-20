<?php

namespace App\Filament\Pages;

use App\Models\Goal;
use App\Models\GoalPeriod;
use Filament\Pages\Page;

class CompanyOkr extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?string $slug = 'company-okr';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.company-okr';

    public ?int $periodId = null;

    public function mount(): void
    {
        $this->periodId = $this->periodId ?? GoalPeriod::active()->orderByDesc('start_date')->value('id')
            ?? GoalPeriod::orderByDesc('start_date')->value('id');
    }

    protected static function getNavigationLabel(): string
    {
        return __('Company OKR');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Performance');
    }

    public function getViewData(): array
    {
        $period = $this->periodId ? GoalPeriod::find($this->periodId) : null;
        $periods = GoalPeriod::orderByDesc('start_date')->get();

        $baseQuery = Goal::query()
            ->with(['keyResults', 'period', 'department', 'owner'])
            ->when($period, fn ($q) => $q->where('period_id', $period->id))
            ->where('visibility', 'public')
            ->whereIn('status', ['active', 'achieved', 'missed'])
            ->orderBy('sort_order')
            ->orderBy('code');

        $companyGoals = (clone $baseQuery)->where('level', 'company')->get();
        $deptGoals = (clone $baseQuery)->where('level', 'department')->get()->groupBy('department_id');
        $individualGoals = (clone $baseQuery)->where('level', 'individual')->get()->groupBy('owner_id');

        // Aggregate overall company achievement from company-level goals
        $companyAchievement = 0.0;
        $totalCompanyWeight = (float) $companyGoals->sum('weight');
        foreach ($companyGoals as $g) {
            $companyAchievement += ((float) $g->weight / 100) * $g->achievement;
        }
        $companyAchievement = round(min(100, max(0, $companyAchievement)), 2);

        return [
            'period' => $period,
            'periods' => $periods,
            'companyGoals' => $companyGoals,
            'deptGoals' => $deptGoals,
            'individualGoals' => $individualGoals,
            'companyAchievement' => $companyAchievement,
            'totalCompanyWeight' => $totalCompanyWeight,
        ];
    }
}

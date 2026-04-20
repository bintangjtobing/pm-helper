<?php

namespace App\Filament\Pages;

use App\Models\Goal;
use App\Models\GoalPeriod;
use App\Models\User;
use Filament\Pages\Page;

class TeamOkr extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $slug = 'team-okr';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.team-okr';

    public ?int $periodId = null;

    public function mount(): void
    {
        $this->periodId = $this->periodId ?? GoalPeriod::active()->orderByDesc('start_date')->value('id')
            ?? GoalPeriod::orderByDesc('start_date')->value('id');
    }

    protected static function getNavigationLabel(): string
    {
        return __('Team OKR');
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

        // Super Admin always sees it; everyone else only if they have direct reports.
        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            return true;
        }

        return User::where('supervisor_id', $user->id)->exists();
    }

    public function getViewData(): array
    {
        $user = auth()->user();
        $period = $this->periodId ? GoalPeriod::find($this->periodId) : null;
        $periods = GoalPeriod::orderByDesc('start_date')->get();

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');

        $subordinatesQuery = $isSuperAdmin
            ? User::query()
            : User::where('supervisor_id', $user->id);

        $subordinates = $subordinatesQuery->orderBy('name')->get();

        $teamGoals = Goal::query()
            ->with(['keyResults', 'period', 'owner'])
            ->when($period, fn ($q) => $q->where('period_id', $period->id))
            ->whereIn('owner_id', $subordinates->pluck('id'))
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->groupBy('owner_id');

        // Per-person roll-up achievement
        $summaries = [];
        foreach ($subordinates as $sub) {
            $goals = $teamGoals->get($sub->id, collect());
            $totalWeight = (float) $goals->sum('weight');
            $achievement = 0.0;
            foreach ($goals as $g) {
                $achievement += ((float) $g->weight / 100) * $g->achievement;
            }

            $summaries[$sub->id] = [
                'user' => $sub,
                'goals' => $goals,
                'total_weight' => $totalWeight,
                'achievement' => round(min(100, max(0, $achievement)), 2),
            ];
        }

        return [
            'period' => $period,
            'periods' => $periods,
            'summaries' => $summaries,
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }
}

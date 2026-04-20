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

    /**
     * Roles that can see EVERY user's OKR (read-only transparency).
     * Executive and Stakeholder sit at leadership / investor level and
     * get full visibility by design.
     */
    protected static array $fullAccessRoles = ['Super Admin', 'Executive', 'Stakeholder'];

    protected static function userHasFullAccess($user): bool
    {
        if (! $user || ! method_exists($user, 'hasAnyRole')) {
            return false;
        }
        return $user->hasAnyRole(self::$fullAccessRoles);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Leadership / stakeholder roles see every user's OKR.
        if (self::userHasFullAccess($user)) {
            return true;
        }

        // Otherwise only show the page to supervisors who have direct reports.
        return User::where('supervisor_id', $user->id)->exists();
    }

    public function getViewData(): array
    {
        $user = auth()->user();
        $period = $this->periodId ? GoalPeriod::find($this->periodId) : null;
        $periods = GoalPeriod::orderByDesc('start_date')->get();

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');
        $hasFullAccess = self::userHasFullAccess($user);

        $subordinatesQuery = $hasFullAccess
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
            'hasFullAccess' => $hasFullAccess,
            'viewerRoleLabel' => $isSuperAdmin
                ? 'Super Admin'
                : ($hasFullAccess
                    ? ($user->roles->pluck('name')->first() ?? 'Leadership')
                    : 'Supervisor'),
        ];
    }
}

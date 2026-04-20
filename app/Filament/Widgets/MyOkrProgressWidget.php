<?php

namespace App\Filament\Widgets;

use App\Models\Goal;
use App\Models\GoalPeriod;
use App\Models\KeyResult;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class MyOkrProgressWidget extends Widget
{
    protected static ?int $sort = 7;

    protected static string $view = 'filament.widgets.my-okr-progress';

    protected int|string|array $columnSpan = [
        'sm' => 2,
        'md' => 6,
        'lg' => 3,
    ];

    public static function canView(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $period = GoalPeriod::active()->first();
        if (! $period) {
            return false;
        }

        return Goal::where('owner_id', auth()->id())
            ->where('period_id', $period->id)
            ->exists();
    }

    public function getViewData(): array
    {
        $userId = auth()->id();
        $period = GoalPeriod::active()->orderByDesc('start_date')->first();

        $goals = Goal::query()
            ->with('keyResults')
            ->where('owner_id', $userId)
            ->where('period_id', $period?->id)
            ->where('type', 'objective')
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        $totalAchievement = 0.0;
        foreach ($goals as $g) {
            $totalAchievement += ((float) $g->weight / 100) * $g->achievement;
        }
        $totalAchievement = round(min(100, max(0, $totalAchievement)), 2);

        // Pending KR updates — KRs with no manual/weekly update in the last 7 days
        $weekAgo = Carbon::now()->subWeek();
        $pendingKrs = KeyResult::query()
            ->whereIn('progress_mode', ['manual', 'hybrid'])
            ->whereHas('goal', fn ($q) => $q
                ->where('owner_id', $userId)
                ->where('period_id', $period?->id))
            ->where(function ($q) use ($weekAgo) {
                $q->whereDoesntHave('updates', fn ($u) => $u
                        ->whereIn('source', ['manual', 'weekly_report'])
                        ->where('created_at', '>=', $weekAgo));
            })
            ->count();

        return [
            'period' => $period,
            'goals' => $goals,
            'totalAchievement' => $totalAchievement,
            'pendingKrs' => $pendingKrs,
        ];
    }
}

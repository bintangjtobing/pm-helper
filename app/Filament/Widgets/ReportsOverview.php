<?php

namespace App\Filament\Widgets;

use App\Models\DailyReport;
use App\Models\User;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class ReportsOverview extends Widget
{
    protected static ?int $sort = 6;
    protected static string $view = 'filament.widgets.reports-overview';

    protected int|string|array $columnSpan = [
        'sm' => 2,
        'md' => 6,
        'lg' => 3
    ];

    public static function canView(): bool
    {
        return auth()->user()->can('List daily reports') || auth()->user()->can('List weekly reports');
    }

    public function getViewData(): array
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        // Team size (active users)
        $teamSize = User::count();

        // Daily reports today
        $dailyToday = DailyReport::where('report_date', $today)
            ->where('status', '!=', 'draft')
            ->count();
        $dailyTodayPct = $teamSize > 0 ? round(($dailyToday / $teamSize) * 100) : 0;

        // Daily reports this week
        $dailyThisWeek = DailyReport::whereBetween('report_date', [$weekStart, $weekEnd])
            ->where('status', '!=', 'draft')
            ->distinct('user_id')
            ->count('user_id');

        // Weekly reports this week
        $weeklyThisWeek = WeeklyReport::where('week_start', $weekStart->format('Y-m-d'))
            ->where('status', '!=', 'draft')
            ->count();
        $weeklyThisWeekPct = $teamSize > 0 ? round(($weeklyThisWeek / $teamSize) * 100) : 0;

        // Pending acknowledgment
        $pendingDaily = DailyReport::where('status', 'submitted')->count();
        $pendingWeekly = WeeklyReport::where('status', 'submitted')->count();
        $pendingTotal = $pendingDaily + $pendingWeekly;

        // This week streak: how many days have at least 1 daily report
        $daysWithReports = DailyReport::whereBetween('report_date', [$weekStart, $today])
            ->where('status', '!=', 'draft')
            ->distinct('report_date')
            ->count('report_date');
        $daysPassed = max(1, $weekStart->diffInDays($today) + 1);
        $streakPct = round(($daysWithReports / $daysPassed) * 100);

        return [
            'dailyToday' => $dailyToday,
            'dailyTodayPct' => $dailyTodayPct,
            'dailyThisWeek' => $dailyThisWeek,
            'weeklyThisWeek' => $weeklyThisWeek,
            'weeklyThisWeekPct' => $weeklyThisWeekPct,
            'pendingTotal' => $pendingTotal,
            'pendingDaily' => $pendingDaily,
            'pendingWeekly' => $pendingWeekly,
            'teamSize' => $teamSize,
            'streakPct' => $streakPct,
            'daysWithReports' => $daysWithReports,
            'daysPassed' => $daysPassed,
        ];
    }
}

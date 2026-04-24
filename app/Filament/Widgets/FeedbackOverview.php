<?php

namespace App\Filament\Widgets;

use App\Models\CustomerFeedback;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class FeedbackOverview extends Widget
{
    protected static ?int $sort = 7;
    protected static string $view = 'filament.widgets.feedback-overview';

    protected int|string|array $columnSpan = [
        'sm' => 2,
        'md' => 6,
        'lg' => 3,
    ];

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) return false;

        return $user->hasRole(['Super Admin', 'Admin', 'Project Manager'])
            || $user->can('List customer feedbacks');
    }

    public function getViewData(): array
    {
        $pendingCount = CustomerFeedback::where('status', 'pending')->count();
        $convertedCount = CustomerFeedback::where('status', 'converted_to_ticket')->count();
        $rejectedCount = CustomerFeedback::where('status', 'rejected')->count();

        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weekSubmitted = CustomerFeedback::whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $weekConverted = CustomerFeedback::whereBetween('created_at', [$weekStart, $weekEnd])
            ->where('status', 'converted_to_ticket')
            ->count();

        $conversionRate = $weekSubmitted > 0
            ? round(($weekConverted / $weekSubmitted) * 100)
            : null;

        $latestPending = CustomerFeedback::with(['user', 'project'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        return [
            'pendingCount' => $pendingCount,
            'convertedCount' => $convertedCount,
            'rejectedCount' => $rejectedCount,
            'weekSubmitted' => $weekSubmitted,
            'weekConverted' => $weekConverted,
            'conversionRate' => $conversionRate,
            'latestPending' => $latestPending,
        ];
    }
}

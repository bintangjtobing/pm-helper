<?php

namespace App\Filament\Widgets;

use App\Models\Discussion;
use Filament\Widgets\Widget;

class DiscussionsOverview extends Widget
{
    protected static ?int $sort = 7;
    protected static string $view = 'filament.widgets.discussions-overview';

    protected int|string|array $columnSpan = [
        'sm' => 2,
        'md' => 6,
        'lg' => 3
    ];

    public static function canView(): bool
    {
        return auth()->user()->can('List discussions');
    }

    public function getViewData(): array
    {
        $openCount = Discussion::where('status', 'open')->count();
        $inDiscussionCount = Discussion::where('status', 'in_discussion')->count();
        $resolvedCount = Discussion::where('status', 'resolved')->count();
        $closedCount = Discussion::where('status', 'closed')->count();
        $totalActive = $openCount + $inDiscussionCount;

        $highPriorityOpen = Discussion::whereIn('status', ['open', 'in_discussion'])
            ->where('priority', 'high')
            ->count();

        // Latest active discussions (open or in_discussion)
        $latestDiscussions = Discussion::with(['user', 'project'])
            ->withCount('replies')
            ->whereIn('status', ['open', 'in_discussion'])
            ->latest()
            ->limit(5)
            ->get();

        return [
            'openCount' => $openCount,
            'inDiscussionCount' => $inDiscussionCount,
            'resolvedCount' => $resolvedCount,
            'closedCount' => $closedCount,
            'totalActive' => $totalActive,
            'highPriorityOpen' => $highPriorityOpen,
            'latestDiscussions' => $latestDiscussions,
        ];
    }
}

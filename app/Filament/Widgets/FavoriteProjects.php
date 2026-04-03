<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\HtmlString;

class FavoriteProjects extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 6
    ];

    protected function getColumns(): int
    {
        return 4;
    }

    public static function canView(): bool
    {
        return auth()->user()->can('List projects');
    }

    protected function getCards(): array
    {
        $favoriteProjects = auth()->user()->favoriteProjects;
        $cards = [];
        foreach ($favoriteProjects as $project) {
            $ticketsCount = $project->tickets()->count();
            $contributorsCount = $project->contributors->count();
            $completedCount = $project->tickets()->whereHas('status', fn($q) => $q->whereIn('name', ['Released', 'Approved', 'QA Passed', 'Ready for Release']))->count();
            $completionRate = $ticketsCount > 0 ? round(($completedCount / $ticketsCount) * 100) : 0;

            $cover = $project->getFirstMediaUrl('cover');
            $initials = strtoupper(substr($project->name, 0, 2));
            $bg = substr(md5($project->name), 0, 6);
            $coverHtml = $cover
                ? '<img src="' . e($cover) . '" class="w-10 h-10 rounded-lg object-cover" loading="lazy" />'
                : '<div class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-lg" style="background:linear-gradient(135deg,#' . $bg . ',#' . substr(md5($project->name . 'x'), 0, 6) . ')">' . $initials . '</div>';

            $cards[] = Card::make('', new HtmlString('
                    <div class="flex items-center gap-3 -mt-2">
                        ' . $coverHtml . '
                        <div>
                            <div class="text-base font-semibold text-gray-900 dark:text-white">' . e($project->name) . '</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">' . e($project->ticket_prefix) . ' &middot; ' . ucfirst($project->type) . '</div>
                        </div>
                    </div>
                '))
                ->color($completionRate >= 60 ? 'success' : ($completionRate >= 30 ? 'warning' : 'primary'))
                ->description(new HtmlString('
                        <div class="flex items-center gap-4 mt-3 text-sm text-gray-500 dark:text-gray-400">
                            <span class="font-medium">' . $ticketsCount . ' <span class="font-normal">' . __('Tickets') . '</span></span>
                            <span class="font-medium">' . $contributorsCount . ' <span class="font-normal">' . __('Members') . '</span></span>
                            <span class="font-medium text-green-500">' . $completionRate . '% <span class="font-normal">' . __('Done') . '</span></span>
                        </div>
                        <div class="flex items-center gap-3 mt-2 text-xs">
                            <a class="text-primary-500 hover:text-primary-600 font-medium"
                               href="' . route('filament.resources.projects.view', $project) . '">' . __('Details') . '</a>
                            <a class="text-primary-500 hover:text-primary-600 font-medium"
                               href="' . route('filament.pages.kanban/{project}', ['project' => $project->id]) . '">' . __('Board') . '</a>
                            <a class="text-primary-500 hover:text-primary-600 font-medium"
                               href="' . route('filament.resources.tickets.index') . '?tableFilters[project_id][values][]=' . $project->id . '">' . __('Tickets') . '</a>
                        </div>
                    '))
                ->chart([
                    max(1, $ticketsCount - $completedCount),
                    (int)($completedCount * 0.3),
                    (int)($completedCount * 0.5),
                    (int)($completedCount * 0.7),
                    (int)($completedCount * 0.85),
                    $completedCount,
                    $completedCount,
                ]);
        }
        return $cards;
    }
}

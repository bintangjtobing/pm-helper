<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DiscussionsOverview;
use App\Filament\Widgets\EnhancedActivityFeed;
use App\Filament\Widgets\GreetingWidget;
use App\Filament\Widgets\FavoriteProjects;
use App\Filament\Widgets\LatestProjects;
use App\Filament\Widgets\LatestTickets;
use App\Filament\Widgets\ReportsOverview;
use App\Filament\Widgets\TicketsOverview;
use App\Filament\Widgets\ProjectAuditOverview;
use App\Filament\Widgets\TimeLoggedByUsers;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static bool $shouldRegisterNavigation = false;

    protected function getColumns(): int | array
    {
        return 6;
    }

    protected function getWidgets(): array
    {
        return [
            GreetingWidget::class,
            ProjectAuditOverview::class,
            FavoriteProjects::class,
            ReportsOverview::class,
            DiscussionsOverview::class,
            EnhancedActivityFeed::class,
            LatestProjects::class,
            LatestTickets::class,
            TicketsOverview::class,
            TimeLoggedByUsers::class,
        ];
    }
}

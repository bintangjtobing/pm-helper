<?php

namespace App\Filament\Resources\WeeklyReportResource\Pages;

use App\Filament\Resources\WeeklyReportResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeeklyReports extends ListRecords
{
    protected static string $resource = WeeklyReportResource::class;

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

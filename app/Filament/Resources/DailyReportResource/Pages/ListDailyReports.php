<?php

namespace App\Filament\Resources\DailyReportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDailyReports extends ListRecords
{
    protected static string $resource = DailyReportResource::class;

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

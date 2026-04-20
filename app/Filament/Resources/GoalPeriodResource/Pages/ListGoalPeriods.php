<?php

namespace App\Filament\Resources\GoalPeriodResource\Pages;

use App\Filament\Resources\GoalPeriodResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGoalPeriods extends ListRecords
{
    protected static string $resource = GoalPeriodResource::class;

    protected function getActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

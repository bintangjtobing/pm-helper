<?php

namespace App\Filament\Resources\DiscussionResource\Pages;

use App\Filament\Resources\DiscussionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscussions extends ListRecords
{
    protected static string $resource = DiscussionResource::class;

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('New Discussion')),
        ];
    }
}

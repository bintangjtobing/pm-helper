<?php

namespace App\Filament\Resources\DiscussionResource\Pages;

use App\Filament\Resources\DiscussionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscussion extends CreateRecord
{
    protected static string $resource = DiscussionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}

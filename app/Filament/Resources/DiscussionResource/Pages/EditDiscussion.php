<?php

namespace App\Filament\Resources\DiscussionResource\Pages;

use App\Filament\Resources\DiscussionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiscussion extends EditRecord
{
    protected static string $resource = DiscussionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->user_id === auth()->id() || auth()->user()->hasRole('Super Admin')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}

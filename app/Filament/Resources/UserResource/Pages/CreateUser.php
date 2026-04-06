<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        // Assign gender avatar if gender was set and no custom avatar
        if ($this->record->gender && in_array($this->record->gender, ['male', 'female'])) {
            $this->record->assignGenderAvatar();
        }
    }
}

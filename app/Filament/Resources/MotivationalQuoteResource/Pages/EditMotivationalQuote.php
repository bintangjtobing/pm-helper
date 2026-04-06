<?php

namespace App\Filament\Resources\MotivationalQuoteResource\Pages;

use App\Filament\Resources\MotivationalQuoteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMotivationalQuote extends EditRecord
{
    protected static string $resource = MotivationalQuoteResource::class;

    protected function getActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}

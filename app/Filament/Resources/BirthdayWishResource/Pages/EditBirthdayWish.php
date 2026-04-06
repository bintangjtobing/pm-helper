<?php
namespace App\Filament\Resources\BirthdayWishResource\Pages;
use App\Filament\Resources\BirthdayWishResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
class EditBirthdayWish extends EditRecord {
    protected static string $resource = BirthdayWishResource::class;
    protected function getActions(): array { return [Actions\DeleteAction::make()]; }
}

<?php

namespace App\Filament\Resources\BirthdayWishResource\Pages;

use App\Filament\Resources\BirthdayWishResource;
use App\Models\BirthdayWish;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBirthdayWishes extends ListRecords
{
    protected static string $resource = BirthdayWishResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('import_csv')
                ->label(__('Import CSV'))
                ->icon('heroicon-o-upload')
                ->color('secondary')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('csv_file')
                        ->label(__('CSV File'))
                        ->helperText(__('CSV with columns: No, Wish, Tone, Audience'))
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                        ->required()->disk('local')->directory('temp'),
                ])
                ->action(function (array $data) {
                    $path = storage_path('app/' . $data['csv_file']);
                    if (!file_exists($path)) { $this->notify('danger', 'File not found'); return; }
                    $handle = fopen($path, 'r');
                    fgetcsv($handle);
                    $count = 0;
                    while (($row = fgetcsv($handle)) !== false) {
                        if (count($row) >= 2 && !empty($row[1])) {
                            BirthdayWish::firstOrCreate(['wish' => $row[1]], ['tone' => $row[2] ?? null, 'is_active' => true]);
                            $count++;
                        }
                    }
                    fclose($handle); unlink($path);
                    $this->notify('success', __(':count wishes imported', ['count' => $count]));
                }),
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\MotivationalQuoteResource\Pages;

use App\Filament\Resources\MotivationalQuoteResource;
use App\Models\MotivationalQuote;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\WithFileUploads;

class ListMotivationalQuotes extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = MotivationalQuoteResource::class;

    public $csvFile;

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
                        ->helperText(__('CSV with columns: No, Quote, Author'))
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel'])
                        ->required()
                        ->disk('local')
                        ->directory('temp'),
                ])
                ->action(function (array $data) {
                    $path = storage_path('app/' . $data['csv_file']);

                    if (!file_exists($path)) {
                        $this->notify('danger', __('File not found'));
                        return;
                    }

                    $handle = fopen($path, 'r');
                    $header = fgetcsv($handle);
                    $count = 0;

                    while (($row = fgetcsv($handle)) !== false) {
                        if (count($row) >= 3 && !empty($row[1])) {
                            MotivationalQuote::firstOrCreate(
                                ['quote' => $row[1]],
                                ['author' => $row[2] ?? null, 'is_active' => true]
                            );
                            $count++;
                        }
                    }

                    fclose($handle);
                    unlink($path);

                    $this->notify('success', __(':count quotes imported', ['count' => $count]));
                }),

            Actions\CreateAction::make(),
        ];
    }
}

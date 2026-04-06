<?php

namespace App\Filament\Resources\DailyReportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use App\Models\DailyReport;
use App\Models\User;
use App\Notifications\DailyReportSubmitted;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyReport extends CreateRecord
{
    protected static string $resource = DailyReportResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->label(__('Submit Report'))
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    $this->form->getState();
                    $this->data['status'] = 'submitted';
                    $this->data['submitted_at'] = now();
                    $this->create();
                }),
        ];
    }

    protected function afterCreate(): void
    {
        if ($this->record->status === 'submitted') {
            $notifyUsers = User::role(['Super Admin', 'Project Manager', 'Stakeholder'])->get();
            foreach ($notifyUsers as $user) {
                $user->notify(new DailyReportSubmitted($this->record));
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

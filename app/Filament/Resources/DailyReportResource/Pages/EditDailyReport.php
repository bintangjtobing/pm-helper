<?php

namespace App\Filament\Resources\DailyReportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use App\Models\User;
use App\Notifications\DailyReportSubmitted;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailyReport extends EditRecord
{
    protected static string $resource = DailyReportResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),

            Actions\Action::make('submit')
                ->label(__('Submit Report'))
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);

                    $notifyUsers = User::role(['Super Admin', 'Project Manager', 'Stakeholder'])->get();
                    foreach ($notifyUsers as $user) {
                        $user->notify(new DailyReportSubmitted($this->record));
                    }

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}

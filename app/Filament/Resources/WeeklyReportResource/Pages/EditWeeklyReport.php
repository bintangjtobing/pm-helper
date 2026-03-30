<?php

namespace App\Filament\Resources\WeeklyReportResource\Pages;

use App\Filament\Resources\WeeklyReportResource;
use App\Models\User;
use App\Notifications\WeeklyReportSubmitted;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeeklyReport extends EditRecord
{
    protected static string $resource = WeeklyReportResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),

            Actions\Action::make('submit')
                ->label(__('Submit Report'))
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading(__('Submit Weekly Report'))
                ->modalSubheading(__('Once submitted, you will not be able to edit this report. Are you sure?'))
                ->action(function () {
                    // Save current form state first
                    $this->save();

                    $this->record->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);

                    // Notify Super Admin and PM
                    $notifyUsers = User::role(['Super Admin', 'Project Manager'])->get();
                    foreach ($notifyUsers as $user) {
                        $user->notify(new WeeklyReportSubmitted($this->record));
                    }

                    $this->redirect(WeeklyReportResource::getUrl('view', ['record' => $this->record]));
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

<?php

namespace App\Filament\Resources\DailyReportResource\Pages;

use App\Filament\Resources\DailyReportResource;
use App\Models\DailyReport;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDailyReport extends ViewRecord
{
    protected static string $resource = DailyReportResource::class;

    protected static string $view = 'filament.resources.daily-reports.view';

    protected function getActions(): array
    {
        $actions = [];

        if ($this->record->user_id === auth()->id() && $this->record->status === 'draft') {
            $actions[] = Actions\EditAction::make();

            $actions[] = Actions\Action::make('submit')
                ->label(__('Submit Report'))
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                });
        }

        if ($this->record->status === 'submitted' && auth()->user()->hasRole(['Super Admin', 'Project Manager'])) {
            $actions[] = Actions\Action::make('acknowledge')
                ->label(__('Acknowledge'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'acknowledged',
                        'acknowledged_by' => auth()->id(),
                        'acknowledged_at' => now(),
                    ]);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                });
        }

        return $actions;
    }
}

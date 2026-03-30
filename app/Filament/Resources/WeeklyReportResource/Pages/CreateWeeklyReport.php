<?php

namespace App\Filament\Resources\WeeklyReportResource\Pages;

use App\Filament\Resources\WeeklyReportResource;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Notifications\WeeklyReportSubmitted;
use App\Services\WeeklyReportService;
use Carbon\Carbon;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWeeklyReport extends CreateRecord
{
    protected static string $resource = WeeklyReportResource::class;

    public function mount(): void
    {
        parent::mount();

        $service = new WeeklyReportService();
        $bounds = $service->getWeekBounds();
        $summary = $service->generateAutoSummary(auth()->user(), $bounds['week_start'], $bounds['week_end']);
        $content = $service->formatSummaryAsMarkdown($summary);

        $this->form->fill([
            'user_id' => auth()->id(),
            'week_start' => $bounds['week_start']->format('Y-m-d'),
            'content' => $content,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = new WeeklyReportService();

        // Calculate week bounds from the selected week_start date
        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $data['user_id'] = auth()->id();
        $data['week_start'] = $weekStart->format('Y-m-d');
        $data['week_end'] = $weekEnd->format('Y-m-d');
        $data['auto_summary'] = $service->generateAutoSummary(auth()->user(), $weekStart, $weekEnd);
        $data['status'] = 'draft';

        return $data;
    }

    protected function getActions(): array
    {
        return [];
    }

    protected function getCreateFormAction(): \Filament\Pages\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label(__('Save as Draft'));
    }

    protected function getCreateAnotherFormAction(): \Filament\Pages\Actions\Action
    {
        return Actions\Action::make('submit')
            ->label(__('Submit Report'))
            ->color('success')
            ->icon('heroicon-o-paper-airplane')
            ->requiresConfirmation()
            ->modalHeading(__('Submit Weekly Report'))
            ->modalSubheading(__('Once submitted, you will not be able to edit this report. Are you sure?'))
            ->action(function () {
                $this->create(false);

                $report = WeeklyReport::where('user_id', auth()->id())
                    ->latest()
                    ->first();

                if ($report) {
                    $report->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);

                    $notifyUsers = User::role(['Super Admin', 'Project Manager'])->get();
                    foreach ($notifyUsers as $user) {
                        $user->notify(new WeeklyReportSubmitted($report));
                    }
                }

                $this->redirect(WeeklyReportResource::getUrl('index'));
            });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

<?php

namespace App\Filament\Resources\WeeklyReportResource\Pages;

use App\Filament\Resources\WeeklyReportResource;
use App\Models\User;
use App\Models\WeeklyReport;
use App\Notifications\WeeklyReportSubmitted;
use App\Services\WeeklyReportService;
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
            'content' => $content,
        ]);

        // Store bounds and summary in session for use in mutate
        session([
            'weekly_report_week_start' => $bounds['week_start']->format('Y-m-d'),
            'weekly_report_week_end' => $bounds['week_end']->format('Y-m-d'),
            'weekly_report_auto_summary' => $summary,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['week_start'] = session('weekly_report_week_start');
        $data['week_end'] = session('weekly_report_week_end');
        $data['auto_summary'] = session('weekly_report_auto_summary');
        $data['status'] = 'draft';

        session()->forget(['weekly_report_week_start', 'weekly_report_week_end', 'weekly_report_auto_summary']);

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

                    // Notify Super Admin and PM
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

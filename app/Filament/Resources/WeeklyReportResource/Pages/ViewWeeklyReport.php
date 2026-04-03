<?php

namespace App\Filament\Resources\WeeklyReportResource\Pages;

use App\Filament\Resources\WeeklyReportResource;
use App\Models\User;
use App\Models\WeeklyReportFeedback;
use App\Models\WeeklyReportView;
use App\Notifications\WeeklyReportSubmitted;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWeeklyReport extends ViewRecord implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = WeeklyReportResource::class;

    protected static string $view = 'filament.resources.weekly-reports.view';

    public string $tab = 'feedback';

    public function mount($record): void
    {
        parent::mount($record);
        $this->form->fill();
        $this->recordView();
    }

    protected function recordView(): void
    {
        if ($this->record->status === 'draft') {
            return;
        }

        WeeklyReportView::updateOrCreate(
            [
                'weekly_report_id' => $this->record->id,
                'user_id' => auth()->id(),
            ],
            [
                'viewed_at' => now(),
            ]
        );
    }

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->user_id === auth()->id() && $this->record->status === 'draft'),

            Actions\Action::make('submit')
                ->label(__('Submit Report'))
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn () => $this->record->user_id === auth()->id() && $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading(__('Submit Weekly Report'))
                ->modalSubheading(__('Once submitted, you will not be able to edit this report. Are you sure?'))
                ->action(function () {
                    $this->record->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);

                    $notifyUsers = User::role(['Super Admin', 'Project Manager'])->get();
                    foreach ($notifyUsers as $user) {
                        $user->notify(new WeeklyReportSubmitted($this->record));
                    }

                    $this->record->refresh();
                    $this->notify('success', __('Report submitted successfully'));
                }),

            Actions\Action::make('acknowledge')
                ->label(__('Acknowledge'))
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () =>
                    $this->record->status === 'submitted' &&
                    auth()->user()->hasRole(['Super Admin', 'Project Manager'])
                )
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'acknowledged',
                        'acknowledged_by' => auth()->id(),
                        'acknowledged_at' => now(),
                    ]);
                    $this->record->refresh();
                    $this->notify('success', __('Report acknowledged'));
                }),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            RichEditor::make('feedback')
                ->disableLabel()
                ->placeholder(__('Write your feedback on this report...'))
                ->required()
                ->toolbarButtons([
                    'bold', 'italic', 'strike', 'link',
                    'orderedList', 'bulletList', 'blockquote',
                    'redo', 'undo',
                ]),
        ];
    }

    public function submitFeedback(): void
    {
        $data = $this->form->getState();

        WeeklyReportFeedback::create([
            'weekly_report_id' => $this->record->id,
            'user_id' => auth()->id(),
            'content' => $data['feedback'],
        ]);

        $this->record->refresh();
        $this->form->fill();
        $this->notify('success', __('Feedback submitted'));
    }

    public function canGiveFeedback(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Stakeholder', 'Project Manager'])
            && $this->record->status !== 'draft';
    }

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
    }
}

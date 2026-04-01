<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\CustomerFeedback;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getActions(): array
    {
        $actions = [
            Actions\Action::make('kanban')
                ->label(
                    fn ()
                    => ($this->record->type === 'scrum' ? __('Scrum board') : __('Kanban board'))
                )
                ->icon('heroicon-o-view-boards')
                ->color('secondary')
                ->url(function () {
                    if ($this->record->type === 'scrum') {
                        return route('filament.pages.scrum/{project}', ['project' => $this->record->id]);
                    } else {
                        return route('filament.pages.kanban/{project}', ['project' => $this->record->id]);
                    }
                }),
        ];

        // Stakeholder: suggest changes instead of direct edit
        if (auth()->user()->hasRole('Stakeholder')) {
            $actions[] = Actions\Action::make('suggestDescriptionChange')
                ->label(__('Suggest Description Change'))
                ->icon('heroicon-o-pencil-alt')
                ->color('warning')
                ->modalHeading(__('Suggest Description Change'))
                ->modalSubheading(__('Your changes will be submitted as feedback for Project Manager approval.'))
                ->modalWidth('4xl')
                ->form([
                    Forms\Components\RichEditor::make('proposed_description')
                        ->label(__('Proposed Description'))
                        ->default($this->record->description)
                        ->required(),
                    Forms\Components\Textarea::make('reason')
                        ->label(__('Reason for Change'))
                        ->placeholder(__('Explain why this change is needed...'))
                        ->rows(3)
                        ->required(),
                ])
                ->action(function (array $data) {
                    CustomerFeedback::create([
                        'project_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'title' => __('Description Change Request') . ' - ' . $this->record->name,
                        'description' => $data['reason'],
                        'status' => 'pending',
                        'change_type' => 'project_description',
                        'proposed_data' => [
                            'field' => 'description',
                            'old_value' => $this->record->description,
                            'new_value' => $data['proposed_description'],
                        ],
                    ]);

                    Filament::notify('success', __('Your description change has been submitted for review.'));
                });

            $actions[] = Actions\Action::make('suggestGoalsChange')
                ->label(__('Suggest Goals Change'))
                ->icon('heroicon-o-light-bulb')
                ->color('warning')
                ->modalHeading(__('Suggest Goals & Requirements Change'))
                ->modalSubheading(__('Your changes will be submitted as feedback for Project Manager approval.'))
                ->modalWidth('4xl')
                ->form([
                    Forms\Components\RichEditor::make('proposed_goals')
                        ->label(__('Proposed Goals & Requirements'))
                        ->default($this->record->goals)
                        ->required(),
                    Forms\Components\Textarea::make('reason')
                        ->label(__('Reason for Change'))
                        ->placeholder(__('Explain why this change is needed...'))
                        ->rows(3)
                        ->required(),
                ])
                ->action(function (array $data) {
                    CustomerFeedback::create([
                        'project_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'title' => __('Goals Change Request') . ' - ' . $this->record->name,
                        'description' => $data['reason'],
                        'status' => 'pending',
                        'change_type' => 'project_goals',
                        'proposed_data' => [
                            'field' => 'goals',
                            'old_value' => $this->record->goals,
                            'new_value' => $data['proposed_goals'],
                        ],
                    ]);

                    Filament::notify('success', __('Your goals change has been submitted for review.'));
                });
        } else {
            $actions[] = Actions\EditAction::make();
        }

        return $actions;
    }
}

<?php

namespace App\Filament\Resources\DiscussionResource\Pages;

use App\Filament\Resources\DiscussionResource;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDiscussion extends ViewRecord
{
    protected static string $resource = DiscussionResource::class;

    protected static string $view = 'filament.resources.discussions.view';

    public string $replyContent = '';

    protected function getActions(): array
    {
        $actions = [];

        if ($this->record->user_id === auth()->id()) {
            $actions[] = Actions\EditAction::make();
        }

        // Status change actions
        if (auth()->user()->hasRole(['Super Admin', 'Project Manager']) || $this->record->user_id === auth()->id()) {
            if (in_array($this->record->status, ['open', 'in_discussion'])) {
                $actions[] = Actions\Action::make('resolve')
                    ->label(__('Mark as Resolved'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->record->update([
                            'status' => 'resolved',
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                        ]);
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    });
            }

            if ($this->record->status !== 'closed') {
                $actions[] = Actions\Action::make('close')
                    ->label(__('Close'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->record->update(['status' => 'closed']);
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    });
            }

            if ($this->record->status === 'closed') {
                $actions[] = Actions\Action::make('reopen')
                    ->label(__('Reopen'))
                    ->icon('heroicon-o-refresh')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->record->update([
                            'status' => 'open',
                            'resolved_by' => null,
                            'resolved_at' => null,
                        ]);
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    });
            }
        }

        return $actions;
    }

    public function submitReply(): void
    {
        if (empty(trim($this->replyContent))) {
            return;
        }

        DiscussionReply::create([
            'discussion_id' => $this->record->id,
            'user_id' => auth()->id(),
            'content' => $this->replyContent,
        ]);

        // Auto-update status to in_discussion if still open
        if ($this->record->status === 'open') {
            $this->record->update(['status' => 'in_discussion']);
        }

        $this->replyContent = '';
        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
    }
}

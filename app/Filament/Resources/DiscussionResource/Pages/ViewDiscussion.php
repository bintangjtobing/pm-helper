<?php

namespace App\Filament\Resources\DiscussionResource\Pages;

use App\Filament\Resources\DiscussionResource;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\User;
use App\Notifications\DiscussionReplied;
use App\Notifications\TicketMentioned;
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

        $reply = DiscussionReply::create([
            'discussion_id' => $this->record->id,
            'user_id' => auth()->id(),
            'content' => $this->replyContent,
        ]);

        // Auto-update status to in_discussion if still open
        if ($this->record->status === 'open') {
            $this->record->update(['status' => 'in_discussion']);
        }

        // Notify discussion author + all previous participants (except replier)
        $participantIds = DiscussionReply::where('discussion_id', $this->record->id)
            ->pluck('user_id')
            ->push($this->record->user_id)
            ->unique()
            ->reject(fn ($id) => $id === auth()->id());

        $notifyUsers = User::whereIn('id', $participantIds)->get();
        foreach ($notifyUsers as $user) {
            $user->notify(new DiscussionReplied($this->record, $reply));
        }

        // Parse @mentions and notify mentioned users
        preg_match_all('/@(\w+)/', $this->replyContent, $matches);
        if (!empty($matches[1])) {
            $mentionedUsers = User::whereIn('username', $matches[1])
                ->where('id', '!=', auth()->id())
                ->get();

            foreach ($mentionedUsers as $mentionedUser) {
                // Create a simple mention notification (reuse discussion reply notification with mention context)
                $mentionedUser->notify(new DiscussionReplied($this->record, $reply));
            }
        }

        $this->replyContent = '';
        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
    }

    public function getMentionUsersJson(): string
    {
        $users = User::whereNotNull('username')
            ->where('username', '!=', '')
            ->get()
            ->map(function ($user) {
                $avatar = $user->getAttributes()['avatar_url']
                    ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=64&background=' . substr(md5($user->id), 0, 6) . '&color=ffffff');

                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'avatar' => $avatar,
                ];
            })
            ->values()
            ->toArray();

        return json_encode($users, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}

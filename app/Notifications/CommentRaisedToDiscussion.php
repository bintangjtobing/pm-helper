<?php

namespace App\Notifications;

use App\Models\Discussion;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentRaisedToDiscussion extends Notification implements ShouldQueue
{
    use Queueable;

    private Discussion $discussion;
    private string $raisedByName;
    private string $ticketCode;

    public function __construct(Discussion $discussion, string $raisedByName, string $ticketCode)
    {
        $this->discussion = $discussion;
        $this->raisedByName = $raisedByName;
        $this->ticketCode = $ticketCode;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $project = $this->discussion->project?->name ?? 'General';

        return (new MailMessage)
            ->subject('Your comment on ' . $this->ticketCode . ' was raised to a discussion')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('**' . $this->raisedByName . '** raised your comment on **' . $this->ticketCode . '** to an open discussion.')
            ->line('**"' . $this->discussion->title . '"**')
            ->line('**Project:** ' . $project . ' · **Priority:** High')
            ->line('Your input is valued — the team wants to discuss this further.')
            ->action('View Discussion', route('filament.resources.discussions.view', $this->discussion->id))
            ->salutation('— PM Helper on Capella Digicrats ID');
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('Your comment was raised to a discussion'))
            ->icon('heroicon-o-speakerphone')
            ->body($this->raisedByName . ' raised your comment on ' . $this->ticketCode . ' to: ' . $this->discussion->title)
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn () => route('filament.resources.discussions.view', $this->discussion->id)),
            ])
            ->getDatabaseMessage();
    }
}

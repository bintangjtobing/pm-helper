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

class DiscussionCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private Discussion $discussion;

    public function __construct(Discussion $discussion)
    {
        $this->discussion = $discussion;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $project = $this->discussion->project?->name ?? 'General';
        $author = $this->discussion->user->name;
        $preview = \Illuminate\Support\Str::limit(
            trim(preg_replace('/\s+/', ' ', preg_replace(['/#{1,6}\s?/', '/\*{1,2}/'], '', strip_tags($this->discussion->content)))),
            150
        );

        $mail = (new MailMessage)
            ->subject('New Discussion: ' . $this->discussion->title . ' — ' . $project)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('**' . $author . '** opened a new discussion.')
            ->line('**"' . $this->discussion->title . '"**')
            ->line('**Project:** ' . $project . ' · **Priority:** ' . ucfirst($this->discussion->priority))
            ->line('> ' . $preview)
            ->action('View Discussion', route('filament.resources.discussions.view', $this->discussion->id))
            ->salutation('— PM Helper on Capella Digicrats ID');

        return $mail;
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('New Discussion'))
            ->icon('heroicon-o-chat-alt-2')
            ->body(fn () => $this->discussion->user->name . ': ' . $this->discussion->title)
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn () => route('filament.resources.discussions.view', $this->discussion->id)),
            ])
            ->getDatabaseMessage();
    }
}

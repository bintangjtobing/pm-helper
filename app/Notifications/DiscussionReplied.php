<?php

namespace App\Notifications;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscussionReplied extends Notification implements ShouldQueue
{
    use Queueable;

    private Discussion $discussion;
    private DiscussionReply $reply;

    public function __construct(Discussion $discussion, DiscussionReply $reply)
    {
        $this->discussion = $discussion;
        $this->reply = $reply;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $replier = $this->reply->user->name;
        $preview = \Illuminate\Support\Str::limit(
            trim(preg_replace('/\s+/', ' ', preg_replace(['/#{1,6}\s?/', '/\*{1,2}/'], '', strip_tags($this->reply->content)))),
            150
        );

        return (new MailMessage)
            ->subject('Re: ' . $this->discussion->title . ' — ' . $replier)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('**' . $replier . '** replied to **"' . $this->discussion->title . '"**:')
            ->line('> ' . $preview)
            ->action('View Discussion', route('filament.resources.discussions.view', $this->discussion->id))
            ->salutation('— PM Helper on Capella Digicrats ID');
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('New Reply on Discussion'))
            ->icon('heroicon-o-reply')
            ->body(fn () => $this->reply->user->name . ' replied to: ' . $this->discussion->title)
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn () => route('filament.resources.discussions.view', $this->discussion->id)),
            ])
            ->getDatabaseMessage();
    }
}

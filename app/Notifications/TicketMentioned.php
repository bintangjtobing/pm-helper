<?php

namespace App\Notifications;

use App\Models\TicketComment;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketMentioned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TicketComment $comment
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $ticket = $this->comment->ticket;
        $mentionedBy = $this->comment->user;
        $preview = \Illuminate\Support\Str::limit(strip_tags($this->comment->content), 200);

        return (new MailMessage)
            ->subject('[' . $ticket->code . '] ' . $mentionedBy->name . ' mentioned you')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('**' . $mentionedBy->name . '** mentioned you in a comment on **' . $ticket->code . '** — ' . $ticket->name . ':')
            ->line('> ' . $preview)
            ->line('**Project:** ' . $ticket->project->name)
            ->action('View Ticket', route('filament.resources.tickets.share', $ticket->code))
            ->salutation('— PM Helper on Capella Digicrats ID');
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title($this->comment->user->name . ' mentioned you')
            ->icon('heroicon-o-at-symbol')
            ->body(fn () => $this->comment->ticket->code . ' — ' . $this->comment->ticket->name)
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn () => route('filament.resources.tickets.share', $this->comment->ticket->code)),
            ])
            ->getDatabaseMessage();
    }
}

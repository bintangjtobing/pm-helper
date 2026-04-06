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

class TicketCommented extends Notification implements ShouldQueue
{
    use Queueable;

    private TicketComment $ticketComment;

    /**
     * Create a new notification instance.
     *
     * @param TicketComment $ticket
     * @return void
     */
    public function __construct(TicketComment $ticketComment)
    {
        $this->ticketComment = $ticketComment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $ticket = $this->ticketComment->ticket;
        $commenter = $this->ticketComment->user;
        $preview = \Illuminate\Support\Str::limit(strip_tags($this->ticketComment->content), 200);

        return (new MailMessage)
            ->subject('[' . $ticket->code . '] New Comment by ' . $commenter->name)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('**' . $commenter->name . '** commented on **' . $ticket->code . '** — ' . $ticket->name . ':')
            ->line('> ' . $preview)
            ->line('**Project:** ' . $ticket->project->name)
            ->action('View Ticket', route('filament.resources.tickets.share', $ticket->code))
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(
                __(
                    'Ticket :ticket commented',
                    [
                        'ticket' => $this->ticketComment->ticket->name
                    ]
                )
            )
            ->icon('heroicon-o-ticket')
            ->body(fn() => __('by :name', ['name' => $this->ticketComment->user->name]))
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn() => route('filament.resources.tickets.share', $this->ticketComment->ticket->code)),
            ])
            ->getDatabaseMessage();
    }
}

<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private Ticket $ticket;

    /**
     * Create a new notification instance.
     *
     * @param Ticket $ticket
     * @return void
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
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
        return (new MailMessage)
            ->subject('[' . $this->ticket->code . '] New Ticket: ' . $this->ticket->name)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('A new ticket has been created in **' . $this->ticket->project->name . '**.')
            ->line('**' . $this->ticket->code . '** — ' . $this->ticket->name)
            ->line('**Owner:** ' . $this->ticket->owner->name . '  ')
            ->line('**Responsible:** ' . ($this->ticket->responsible?->name ?? '—') . '  ')
            ->line('**Status:** ' . $this->ticket->status->name . ' · **Type:** ' . $this->ticket->type->name . ' · **Priority:** ' . $this->ticket->priority->name)
            ->action('View Ticket', route('filament.resources.tickets.share', $this->ticket->code))
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('New ticket created'))
            ->icon('heroicon-o-ticket')
            ->body(fn() => $this->ticket->name)
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn() => route('filament.resources.tickets.share', $this->ticket->code)),
            ])
            ->getDatabaseMessage();
    }
}

<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    private Ticket $ticket;
    private ?TicketActivity $activity;

    /**
     * Create a new notification instance.
     *
     * @param Ticket $ticket
     * @return void
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->activity = $this->ticket->activities()->latest()->first();
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
        $oldStatus = $this->activity?->oldStatus->name ?? '—';
        $newStatus = $this->activity?->newStatus->name ?? $this->ticket->status->name;
        $updatedBy = $this->activity?->user->name ?? 'System';

        return (new MailMessage)
            ->subject('[' . $this->ticket->code . '] Status: ' . $oldStatus . ' → ' . $newStatus)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('The status of **' . $this->ticket->code . '** — ' . $this->ticket->name . ' has been updated.')
            ->line('**' . $oldStatus . '** → **' . $newStatus . '**')
            ->line('**Updated by:** ' . $updatedBy . '  ')
            ->line('**Project:** ' . $this->ticket->project->name)
            ->action('View Ticket', route('filament.resources.tickets.share', $this->ticket->code))
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('Ticket status updated'))
            ->icon('heroicon-o-ticket')
            ->body(
                fn() => __('Old status: :oldStatus - New status: :newStatus', [
                    'oldStatus' => $this->activity?->oldStatus->name ?? '-',
                    'newStatus' => $this->activity?->newStatus->name ?? $this->ticket->status->name,
                ])
            )
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn() => route('filament.resources.tickets.share', $this->ticket->code)),
            ])
            ->getDatabaseMessage();
    }
}
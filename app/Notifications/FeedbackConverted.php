<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\CustomerFeedback;
use Filament\Notifications\Notification as FilamentNotification;
use App\Models\User;
use Filament\Notifications\Actions\Action;

class FeedbackConverted extends Notification implements ShouldQueue
{
    use Queueable;

    private CustomerFeedback $feedback;

    public function __construct(CustomerFeedback $feedback)
    {
        $this->feedback = $feedback;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $ticket = $this->feedback->convertedTicket;

        return (new MailMessage)
            ->subject('Feedback Converted → [' . $ticket->code . '] ' . $this->feedback->title)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your feedback **"' . $this->feedback->title . '"** has been converted into a ticket.')
            ->line('**Ticket:** ' . $ticket->code . '  ')
            ->line('**Project:** ' . $this->feedback->project->name)
            ->line('You will receive updates as the ticket progresses.')
            ->action('View Ticket', route('filament.resources.tickets.share', $ticket->code))
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('Feedback Converted to Ticket'))
            ->icon('heroicon-o-ticket')
            ->body(fn() => $this->feedback->title)
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn() => route('filament.resources.tickets.share', $this->feedback->convertedTicket->code)),
            ])
            ->getDatabaseMessage();
    }
}

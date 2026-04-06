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

class FeedbackUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    private CustomerFeedback $feedback;
    private string $updateMessage;

    public function __construct(CustomerFeedback $feedback, string $updateMessage)
    {
        $this->feedback = $feedback;
        $this->updateMessage = $updateMessage;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Feedback Updated: ' . $this->feedback->title)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your feedback **"' . $this->feedback->title . '"** has been updated.')
            ->line('> ' . $this->updateMessage)
            ->line('**Project:** ' . $this->feedback->project->name)
            ->action('View Feedback', route('filament.resources.customer-feedbacks.view', $this->feedback->id))
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('Feedback Updated'))
            ->icon('heroicon-o-annotation')
            ->body(fn() => $this->updateMessage)
            ->getDatabaseMessage();
    }
}

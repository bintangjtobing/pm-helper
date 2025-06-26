<?php

namespace App\Notifications;

use App\Models\CustomerFeedback;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackSubmitted extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('New Customer Feedback Submitted')
            ->greeting('Hello!')
            ->line('A new customer feedback has been submitted.')
            ->line('**Project:** ' . $this->feedback->project->name)
            ->line('**Customer:** ' . $this->feedback->user->name)
            ->line('**Title:** ' . $this->feedback->title)
            ->line('Please review and take appropriate action.')
            ->action('View Feedback', route('filament.resources.customer-feedbacks.view', $this->feedback->id))
            ->line('Thank you for your attention!')
            ->salutation('Regards, ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('New Customer Feedback'))
            ->icon('heroicon-o-annotation')
            ->body(fn() => $this->feedback->title)
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn() => route('filament.resources.customer-feedbacks.view', $this->feedback->id)),
            ])
            ->getDatabaseMessage();
    }
}

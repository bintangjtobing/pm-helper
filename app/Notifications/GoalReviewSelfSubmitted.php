<?php

namespace App\Notifications;

use App\Models\GoalReview;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoalReviewSelfSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private GoalReview $review) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $employee = $this->review->user?->name ?? 'Employee';
        $period = $this->review->period?->name ?? 'Period';

        return (new MailMessage)
            ->subject("OKR Review awaiting your feedback — {$employee} ({$period})")
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line("**{$employee}** has submitted their self-review for **{$period}**.")
            ->line('Please open Team Reviews to finalize the scores and add your feedback.')
            ->action('Open Team Reviews', url('/team-reviews'))
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        $employee = $this->review->user?->name ?? 'Employee';
        $period = $this->review->period?->name ?? 'Period';

        return FilamentNotification::make()
            ->title('Self-review submitted')
            ->icon('heroicon-o-clipboard-check')
            ->body(fn () => "{$employee} — {$period}")
            ->actions([
                Action::make('review')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn () => url('/team-reviews')),
            ])
            ->getDatabaseMessage();
    }
}

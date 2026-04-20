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

class GoalReviewDisputed extends Notification implements ShouldQueue
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
            ->subject("Review disputed — {$employee} ({$period})")
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line("**{$employee}** has disputed their finalized review for **{$period}**.")
            ->line('Please meet with them to discuss and re-review as needed.')
            ->action('Open Team Reviews', url('/team-reviews'))
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        $employee = $this->review->user?->name ?? 'Employee';
        $period = $this->review->period?->name ?? 'Period';

        return FilamentNotification::make()
            ->title('Review disputed')
            ->icon('heroicon-o-exclamation')
            ->body(fn () => "{$employee} disputed their {$period} review")
            ->actions([
                Action::make('review')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn () => url('/team-reviews')),
            ])
            ->getDatabaseMessage();
    }
}

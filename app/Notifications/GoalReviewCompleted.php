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

class GoalReviewCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private GoalReview $review) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $period = $this->review->period?->name ?? 'Period';
        $finalScore = number_format((float) $this->review->final_score, 1);

        return (new MailMessage)
            ->subject("Your OKR Review is final — {$period} ({$finalScore}%)")
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line("Your performance review for **{$period}** has been finalized.")
            ->line("**Final score:** {$finalScore}%")
            ->line('Open My Review to see per-KR breakdown and supervisor feedback.')
            ->action('View My Review', url('/my-review'))
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        $period = $this->review->period?->name ?? 'Period';
        $finalScore = number_format((float) $this->review->final_score, 1);

        return FilamentNotification::make()
            ->title('Review completed')
            ->icon('heroicon-o-clipboard-check')
            ->body(fn () => "{$period} — Final {$finalScore}%")
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn () => url('/my-review')),
            ])
            ->getDatabaseMessage();
    }
}

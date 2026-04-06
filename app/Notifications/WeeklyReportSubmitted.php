<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\WeeklyReport;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyReportSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    private WeeklyReport $report;

    public function __construct(WeeklyReport $report)
    {
        $this->report = $report;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $weekLabel = $this->report->week_start->format('M d') . ' – ' . $this->report->week_end->format('M d, Y');
        $project = $this->report->project?->name ?? 'General';

        return (new MailMessage)
            ->subject('Weekly Report: ' . $project . ' (' . $weekLabel . ') — ' . $this->report->user->name)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('**' . $this->report->user->name . '** submitted a weekly report.')
            ->line('**Week:** ' . $weekLabel . '  ')
            ->line('**Project:** ' . $project)
            ->line('Please review and acknowledge the report.')
            ->action('View Report', route('filament.resources.weekly-reports.view', $this->report->id))
            ->salutation('— ' . config('app.name'));
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('Weekly Report Submitted'))
            ->icon('heroicon-o-document-text')
            ->body(fn () => $this->report->user->name . ' — ' . $this->report->week_start->format('M d') . ' - ' . $this->report->week_end->format('M d, Y'))
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn () => route('filament.resources.weekly-reports.view', $this->report->id)),
            ])
            ->getDatabaseMessage();
    }
}

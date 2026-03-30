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
        return (new MailMessage)
            ->subject('Weekly Report Submitted by ' . $this->report->user->name)
            ->greeting('Hello!')
            ->line('A weekly report has been submitted.')
            ->line('**Author:** ' . $this->report->user->name)
            ->line('**Week:** ' . $this->report->week_start->format('M d') . ' - ' . $this->report->week_end->format('M d, Y'))
            ->line('**Project:** ' . ($this->report->project?->name ?? 'General'))
            ->action('View Report', route('filament.resources.weekly-reports.view', $this->report->id))
            ->line('Please review the report.')
            ->salutation('Regards, ' . config('app.name'));
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

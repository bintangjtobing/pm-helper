<?php

namespace App\Notifications;

use App\Models\DailyReport;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyReportSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    private DailyReport $report;

    public function __construct(DailyReport $report)
    {
        $this->report = $report;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $dateLabel = $this->report->report_date->format('D, d M Y');
        $project = $this->report->project?->name ?? 'General';
        $author = $this->report->user->name;

        $mail = (new MailMessage)
            ->subject('Daily Report: ' . $project . ' (' . $dateLabel . ') — ' . $author)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('**' . $author . '** submitted a daily report.')
            ->line('**Date:** ' . $dateLabel)
            ->line('**Project:** ' . $project);

        if ($this->report->accomplished) {
            $preview = \Illuminate\Support\Str::limit(strip_tags($this->report->accomplished), 150);
            $mail->line('**Accomplished:** ' . $preview);
        }

        if ($this->report->blockers) {
            $mail->line('**Blockers:** ' . \Illuminate\Support\Str::limit(strip_tags($this->report->blockers), 100));
        }

        $mail->action('View Report', route('filament.resources.daily-reports.view', $this->report->id))
            ->salutation('— PM Helper on Capella Digicrats ID');

        return $mail;
    }

    public function toDatabase(User $notifiable): array
    {
        $dateLabel = $this->report->report_date->format('D, d M Y');

        return FilamentNotification::make()
            ->title(__('Daily Report Submitted'))
            ->icon('heroicon-o-calendar')
            ->body(fn () => $this->report->user->name . ' — ' . $dateLabel)
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn () => route('filament.resources.daily-reports.view', $this->report->id)),
            ])
            ->getDatabaseMessage();
    }
}

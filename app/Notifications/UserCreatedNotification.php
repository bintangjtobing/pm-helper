<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public User $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database'];

        // Only include mail channel if SMTP is configured
        if (config('mail.mailers.smtp.host') && config('mail.mailers.smtp.host') !== 'mailpit') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' — Verify Your Account')
            ->greeting('Welcome, ' . $notifiable->name . '!')
            ->line('Your account on **' . config('app.name') . '** has been created.')
            ->line('To get started, please verify your account and set your password by clicking the button below.')
            ->action('Verify My Account', route('validate-account', $this->user->creation_token))
            ->line('If you did not expect this invitation, you can safely ignore this email.')
            ->salutation('— ' . config('app.name'));
    }

    /**
     * Get the database representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => __('Validate your account'),
            'body' => __('Welcome to :app platform. Please validate your account.', ['app' => config('app.name')]),
            'url' => route('validate-account', $this->user->creation_token),
            'user_id' => $this->user->id,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}

<?php

namespace App\Notifications;

use App\Models\TicketComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketMentioned extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        public TicketComment $comment
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $ticket = $this->comment->ticket;
        $mentionedBy = $this->comment->user;
        $preview = \Illuminate\Support\Str::limit(strip_tags($this->comment->content), 200);

        return (new MailMessage)
            ->subject('[' . $ticket->code . '] ' . $mentionedBy->name . ' mentioned you')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('**' . $mentionedBy->name . '** mentioned you in a comment on **' . $ticket->code . '** — ' . $ticket->name . ':')
            ->line('> ' . $preview)
            ->line('**Project:** ' . $ticket->project->name)
            ->action('View Ticket', route('filament.resources.tickets.view', $ticket->id))
            ->salutation('— ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->comment->ticket->id,
            'ticket_code' => $this->comment->ticket->code,
            'ticket_name' => $this->comment->ticket->name,
            'comment_id' => $this->comment->id,
            'mentioned_by' => $this->comment->user->name,
            'comment_content' => strip_tags($this->comment->content),
        ];
    }
}

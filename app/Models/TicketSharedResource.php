<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSharedResource extends Model
{
    protected $fillable = [
        'ticket_id',
        'comment_id',
        'attachment_id',
        'user_id',
        'source',
        'kind',
        'url',
        'title',
        'host',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(TicketComment::class, 'comment_id');
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(TicketCommentAttachment::class, 'attachment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

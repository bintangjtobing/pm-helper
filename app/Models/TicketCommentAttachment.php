<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketCommentAttachment extends Model
{
    protected $fillable = [
        'comment_id',
        'filename_stored',
        'filename_original',
        'mime_type',
        'size_bytes',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function (TicketCommentAttachment $item) {
            $comment = $item->comment;
            if (! $comment) {
                return;
            }

            TicketSharedResource::create([
                'ticket_id' => $comment->ticket_id,
                'comment_id' => $comment->id,
                'attachment_id' => $item->id,
                'user_id' => $comment->user_id,
                'source' => 'attachment',
                'kind' => \App\Support\SharedResourceExtractor::kindFromMime($item->mime_type),
                'url' => '/storage/comment-videos/' . $comment->id . '/' . $item->filename_stored,
                'title' => $item->filename_original,
                'host' => null,
            ]);
        });
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(TicketComment::class, 'comment_id');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }
}

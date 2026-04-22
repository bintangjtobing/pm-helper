<?php

namespace App\Models;

use App\Notifications\TicketCommented;
use App\Notifications\TicketCreated;
use App\Notifications\TicketStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use TicketMentioned;

class TicketComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'ticket_id', 'content'
    ];


    public static function boot()
    {
        parent::boot();

        static::created(function (TicketComment $item) {
            // Parse mentions from content
            $mentionedUsers = self::extractMentions($item->content);

            if (!empty($mentionedUsers)) {
                // Save mentions
                $item->mentions()->attach($mentionedUsers);

                // Send notifications to mentioned users
                foreach ($mentionedUsers as $userId) {
                    $user = User::find($userId);
                    if ($user) {
                        $user->notify(new \App\Notifications\TicketMentioned($item));
                    }
                }
            }

            // Original notification logic
            foreach ($item->ticket->watchers as $user) {
                $user->notify(new TicketCommented($item));
            }
        });

        static::saved(function (TicketComment $item) {
            if (! $item->wasRecentlyCreated && ! $item->wasChanged('content')) {
                return;
            }

            TicketSharedResource::where('comment_id', $item->id)
                ->where('source', 'comment')
                ->delete();

            foreach (\App\Support\SharedResourceExtractor::extractFromHtml($item->content) as $l) {
                TicketSharedResource::create([
                    'ticket_id' => $item->ticket_id,
                    'comment_id' => $item->id,
                    'user_id' => $item->user_id,
                    'source' => 'comment',
                    'kind' => $l['kind'],
                    'url' => $l['url'],
                    'title' => $l['title'],
                    'host' => $l['host'],
                ]);
            }
        });
    }
    private static function extractMentions($content)
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        $usernames = $matches[1];

        if (!empty($usernames)) {
            // Cari berdasarkan username yang baru saja kita tambahkan
            return User::whereIn('username', $usernames)
                      ->pluck('id')
                      ->toArray();
        }

        return [];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }
    public function mentions(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_comment_mentions', 'comment_id', 'user_id')
                    ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketCommentAttachment::class, 'comment_id');
    }
}
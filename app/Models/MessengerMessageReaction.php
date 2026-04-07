<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessengerMessageReaction extends Model
{
    use HasFactory;

    /**
     * Allowed emoji set for reactions (limited).
     */
    public const ALLOWED_EMOJIS = ['👍', '❤️', '😂', '😮', '😢', '🙏'];

    protected $fillable = [
        'message_id',
        'user_id',
        'emoji',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MessengerMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function isAllowed(string $emoji): bool
    {
        return in_array($emoji, self::ALLOWED_EMOJIS, true);
    }
}

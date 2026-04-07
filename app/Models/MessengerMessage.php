<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessengerMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'reply_to_id',
        'edited_at',
        'hidden_for_user_ids',
        'deleted_for_everyone_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
        'deleted_for_everyone_at' => 'datetime',
        'hidden_for_user_ids' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(MessengerConversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(MessengerMessage::class, 'reply_to_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessengerMessageAttachment::class, 'message_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(MessengerMessageRead::class, 'message_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessengerMessageReaction::class, 'message_id');
    }

    public function isDeletedForEveryone(): bool
    {
        return $this->deleted_for_everyone_at !== null;
    }

    public function isHiddenFor(int $userId): bool
    {
        $hidden = $this->hidden_for_user_ids ?? [];
        return in_array($userId, $hidden, true);
    }

    public function isWithinEditWindow(): bool
    {
        return $this->created_at->diffInMinutes(now()) < 15;
    }

    public function isReadBy(int $userId): bool
    {
        return $this->reads()->where('user_id', $userId)->exists();
    }
}

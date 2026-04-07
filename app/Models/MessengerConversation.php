<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessengerConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'attachment_folder',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MessengerMessage::class, 'conversation_id');
    }

    public function latestMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MessengerMessage::class, 'conversation_id')->latestOfMany();
    }

    /**
     * Get the other participant given the current user.
     */
    public function otherParticipant(int $currentUserId): ?User
    {
        if ($currentUserId === $this->user_one_id) {
            return $this->userTwo;
        }
        if ($currentUserId === $this->user_two_id) {
            return $this->userOne;
        }
        return null;
    }

    /**
     * Check if a given user is part of this conversation.
     */
    public function hasParticipant(int $userId): bool
    {
        return $userId === $this->user_one_id || $userId === $this->user_two_id;
    }
}

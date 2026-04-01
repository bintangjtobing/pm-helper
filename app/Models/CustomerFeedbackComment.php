<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFeedbackComment extends Model
{
    protected $fillable = ['feedback_id', 'user_id', 'content'];

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(CustomerFeedback::class, 'feedback_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

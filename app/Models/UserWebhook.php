<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWebhook extends Model
{
    use HasFactory;

    public const EVENT_QA_FAILED = 'qa_failed';

    protected $fillable = [
        'user_id',
        'event',
        'url',
        'secret',
        'project_ids',
        'is_active',
        'last_fired_at',
        'last_status',
        'last_error',
    ];

    protected $casts = [
        'project_ids' => 'array',
        'is_active' => 'boolean',
        'last_fired_at' => 'datetime',
        'last_status' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchesProject(?int $projectId): bool
    {
        if (empty($this->project_ids)) {
            return true;
        }
        return in_array((int) $projectId, array_map('intval', (array) $this->project_ids), true);
    }
}

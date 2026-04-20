<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoalReview extends Model
{
    use HasFactory;

    public const STATUS_PENDING_SELF = 'pending_self';
    public const STATUS_PENDING_SUPERVISOR = 'pending_supervisor';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DISPUTED = 'disputed';

    protected $fillable = [
        'period_id',
        'user_id',
        'supervisor_id',
        'system_score',
        'self_score',
        'final_score',
        'self_narrative',
        'supervisor_feedback',
        'status',
        'self_submitted_at',
        'supervisor_reviewed_at',
        'acknowledged_at',
    ];

    protected $casts = [
        'system_score' => 'decimal:2',
        'self_score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'self_submitted_at' => 'datetime',
        'supervisor_reviewed_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(GoalPeriod::class, 'period_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function keyResultReviews(): HasMany
    {
        return $this->hasMany(KeyResultReview::class);
    }

    public function isSelfSubmittable(): bool
    {
        return $this->status === self::STATUS_PENDING_SELF;
    }

    public function isSupervisorReviewable(): bool
    {
        return $this->status === self::STATUS_PENDING_SUPERVISOR;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING_SELF => ['Pending self-review', 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300'],
            self::STATUS_PENDING_SUPERVISOR => ['Pending supervisor', 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300'],
            self::STATUS_COMPLETED => ['Completed', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300'],
            self::STATUS_DISPUTED => ['Disputed', 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300'],
            default => [ucfirst($this->status), 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-300'],
        };
    }
}

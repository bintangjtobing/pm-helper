<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyResultReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_review_id',
        'key_result_id',
        'snapshot_current',
        'snapshot_target',
        'system_score',
        'self_score',
        'final_score',
        'notes',
    ];

    protected $casts = [
        'snapshot_current' => 'decimal:2',
        'snapshot_target' => 'decimal:2',
        'system_score' => 'decimal:2',
        'self_score' => 'decimal:2',
        'final_score' => 'decimal:2',
    ];

    public function goalReview(): BelongsTo
    {
        return $this->belongsTo(GoalReview::class);
    }

    public function keyResult(): BelongsTo
    {
        return $this->belongsTo(KeyResult::class);
    }

    /**
     * Prefer final_score, then self_score, then system_score.
     */
    public function effectiveScore(): float
    {
        return (float) ($this->final_score ?? $this->self_score ?? $this->system_score ?? 0);
    }
}

<?php

namespace App\Models;

use App\Services\GoalWeightValidator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Goal extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Goal $goal) {
            // Only enforce the per-user budget for Individual Objectives.
            if ($goal->type !== 'objective' || $goal->level !== 'individual') {
                return;
            }
            if (! $goal->owner_id || ! $goal->period_id) {
                return;
            }

            $other = GoalWeightValidator::userObjectivesTotal(
                (int) $goal->owner_id,
                (int) $goal->period_id,
                $goal->exists ? (int) $goal->id : null,
            );

            $total = $other + (float) $goal->weight;
            if ($total > 100.0 + GoalWeightValidator::TOLERANCE) {
                $remaining = max(0, 100.0 - $other);
                throw ValidationException::withMessages([
                    'weight' => "Only {$remaining}% weight budget left for this owner in this period (already allocated: {$other}%).",
                ]);
            }
        });
    }

    protected $fillable = [
        'period_id',
        'type',
        'level',
        'parent_id',
        'department_id',
        'owner_id',
        'code',
        'title',
        'description',
        'weight',
        'status',
        'visibility',
        'sort_order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(GoalPeriod::class, 'period_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Goal::class, 'parent_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function keyResults(): HasMany
    {
        return $this->hasMany(KeyResult::class)->orderBy('sort_order');
    }

    /**
     * Achievement % for this goal = weighted sum of child KR progress.
     * Range: 0–100 (clamped).
     */
    public function getAchievementAttribute(): float
    {
        $krs = $this->keyResults;
        if ($krs->isEmpty()) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($krs as $kr) {
            $total += ((float) $kr->weight / 100) * $kr->progress_percent;
        }

        return round(min(100, max(0, $total)), 2);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('period_id', $periodId);
    }

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeObjectives($query)
    {
        return $query->where('type', 'objective');
    }

    public function scopeKpis($query)
    {
        return $query->where('type', 'kpi');
    }
}

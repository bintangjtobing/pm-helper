<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeyResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'code',
        'title',
        'how_to_measure',
        'weight',
        'target_value',
        'current_value',
        'unit',
        'direction',
        'progress_mode',
        'auto_source',
        'auto_formula',
        'alignment_note',
        'sort_order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'auto_formula' => 'array',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(KeyResultUpdate::class)->orderByDesc('created_at');
    }

    public function isAuto(): bool
    {
        return in_array($this->progress_mode, ['auto', 'hybrid']);
    }

    /**
     * Record a new progress value and return the created update row.
     * Also mutates current_value on the KR itself.
     */
    public function recordUpdate(float $value, string $source = 'manual', ?int $userId = null, ?string $note = null, ?string $weekStart = null): KeyResultUpdate
    {
        $previous = $this->current_value;
        $this->current_value = $value;
        $this->save();

        return $this->updates()->create([
            'user_id' => $userId,
            'value' => $value,
            'previous_value' => $previous,
            'note' => $note,
            'source' => $source,
            'week_start' => $weekStart,
        ]);
    }

    /**
     * Progress % (0–100), respecting direction.
     * increase: current/target
     * decrease: (target/current) — lower is better, target becomes the ceiling
     * maintain: 100 if current >= target else current/target
     */
    public function getProgressPercentAttribute(): float
    {
        $target = (float) $this->target_value;
        $current = (float) $this->current_value;

        if ($target == 0.0) {
            return 0.0;
        }

        $pct = match ($this->direction) {
            'decrease' => $current == 0.0 ? 100.0 : ($target / $current) * 100,
            'maintain' => $current >= $target ? 100.0 : ($current / $target) * 100,
            default => ($current / $target) * 100, // increase
        };

        return round(min(100, max(0, $pct)), 2);
    }
}

<?php

namespace App\Models;

use App\Services\Goals\GoalReviewGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoalPeriod extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::updated(function (GoalPeriod $period) {
            // Auto-generate reviews when a period transitions to closed.
            if ($period->wasChanged('status') && $period->status === 'closed') {
                app(GoalReviewGenerator::class)->generate($period);
            }
        });
    }

    protected $fillable = [
        'name',
        'type',
        'start_date',
        'end_date',
        'status',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class, 'period_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}

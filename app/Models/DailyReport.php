<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'report_date',
        'accomplished',
        'plans',
        'blockers',
        'auto_summary',
        'status',
        'submitted_at',
        'acknowledged_by',
        'acknowledged_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'auto_summary' => 'array',
        'submitted_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function getDateLabelAttribute(): string
    {
        return $this->report_date->format('D, d M Y');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Draft</span>',
            'submitted' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Submitted</span>',
            'acknowledged' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Acknowledged</span>',
            default => $this->status,
        };
    }
}

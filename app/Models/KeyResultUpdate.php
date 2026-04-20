<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyResultUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key_result_id',
        'user_id',
        'value',
        'previous_value',
        'note',
        'source',
        'week_start',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'previous_value' => 'decimal:2',
        'week_start' => 'date',
    ];

    public function keyResult(): BelongsTo
    {
        return $this->belongsTo(KeyResult::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

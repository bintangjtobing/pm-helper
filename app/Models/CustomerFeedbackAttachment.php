<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CustomerFeedbackAttachment extends Model
{
    protected $fillable = [
        'feedback_id',
        'filename_stored',
        'filename_original',
        'mime_type',
        'size_bytes',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function (CustomerFeedbackAttachment $item) {
            $path = 'feedback-attachments/' . $item->feedback_id . '/' . $item->filename_stored;
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(CustomerFeedback::class, 'feedback_id');
    }

    public function getUrlAttribute(): string
    {
        return '/storage/feedback-attachments/' . $this->feedback_id . '/' . $this->filename_stored;
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->size_bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isDocx(): bool
    {
        return $this->mime_type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    }
}

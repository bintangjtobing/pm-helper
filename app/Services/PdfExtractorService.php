<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PdfExtractorService
{
    /**
     * Extract text from a media item (PDF).
     * Results are cached by media ID + last modified time.
     */
    public function extractFromMedia(Media $media, int $maxChars = 4000): string
    {
        $cacheKey = 'pdf_text_' . $media->id . '_' . $media->updated_at->timestamp;

        return Cache::remember($cacheKey, 3600, function () use ($media, $maxChars) {
            return $this->extractFromPath($media->getPath(), $maxChars);
        });
    }

    /**
     * Extract text from a PDF file path.
     */
    public function extractFromPath(string $path, int $maxChars = 4000): string
    {
        try {
            if (!file_exists($path)) {
                return '[File not found]';
            }

            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();

            // Clean up extracted text
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);

            if (empty($text)) {
                return '[No readable text in PDF]';
            }

            // Limit text length
            if (mb_strlen($text) > $maxChars) {
                $text = mb_substr($text, 0, $maxChars) . '... [truncated]';
            }

            return $text;
        } catch (\Exception $e) {
            Log::warning('PDF extraction failed', ['path' => $path, 'error' => $e->getMessage()]);
            return '[Could not extract text from PDF]';
        }
    }
}

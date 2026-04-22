<?php

namespace App\Support;

class SharedResourceExtractor
{
    /**
     * Extract every unique HTTP(S) URL from an HTML/plain string.
     * Returns array of ['url', 'title', 'host', 'kind'].
     */
    public static function extractFromHtml(?string $html): array
    {
        if (! $html || trim($html) === '') {
            return [];
        }

        $decoded = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
        $results = [];
        $seen = [];

        // <a href="…">label</a>
        if (preg_match_all('/<a[^>]+href\s*=\s*"([^"]+)"[^>]*>(.*?)<\/a>/is', $decoded, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $url = trim($m[1]);
                if (! self::isValidUrl($url) || isset($seen[$url])) {
                    continue;
                }
                $seen[$url] = true;

                $text = trim(strip_tags($m[2]));
                $results[] = [
                    'url' => $url,
                    'title' => $text !== '' && $text !== $url ? $text : null,
                    'host' => parse_url($url, PHP_URL_HOST) ?: null,
                    'kind' => self::detectKind($url),
                ];
            }
        }

        // Plain-text URLs (not wrapped in <a>)
        $stripped = strip_tags($decoded);
        if (preg_match_all('/https?:\/\/[^\s<>"\'()]+/i', $stripped, $bare)) {
            foreach ($bare[0] as $url) {
                $url = rtrim($url, '.,;:!?)');
                if (! self::isValidUrl($url) || isset($seen[$url])) {
                    continue;
                }
                $seen[$url] = true;

                $results[] = [
                    'url' => $url,
                    'title' => null,
                    'host' => parse_url($url, PHP_URL_HOST) ?: null,
                    'kind' => self::detectKind($url),
                ];
            }
        }

        return $results;
    }

    public static function isValidUrl(string $url): bool
    {
        if (! preg_match('/^https?:\/\//i', $url)) {
            return false;
        }
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function detectKind(string $url): string
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
        $path = strtolower((string) (parse_url($url, PHP_URL_PATH) ?? ''));
        $full = $host . $path;

        if (preg_match('/(?:^|\.)(?:staging|stage|dev|uat|preview|test)\./', $host)
            || str_contains($full, '/staging')) {
            return 'staging';
        }
        if (str_contains($host, 'figma.com') || str_contains($host, 'penpot')
            || str_contains($host, 'framer.')) {
            return 'design';
        }
        if (str_contains($host, 'github.') || str_contains($host, 'gitlab.')
            || str_contains($host, 'bitbucket.')) {
            return 'repo';
        }
        if (str_starts_with($host, 'api.') || str_contains($full, '/api')
            || str_contains($full, 'swagger') || str_contains($host, 'postman')) {
            return 'api';
        }
        if (str_contains($host, 'notion.') || str_contains($host, 'confluence.')
            || str_contains($host, 'readme.') || str_contains($host, 'docs.google.')
            || str_contains($full, 'docs')) {
            return 'docs';
        }
        if (str_contains($host, 'slack.') || str_contains($host, 'discord.')
            || str_contains($host, 't.me')) {
            return 'chat';
        }

        return 'default';
    }

    public static function kindFromMime(string $mime): string
    {
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        return 'file';
    }
}

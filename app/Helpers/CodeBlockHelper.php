<?php

namespace App\Helpers;

class CodeBlockHelper
{
    /**
     * Auto-detect code-like content in plain text and wrap it in markdown
     * code blocks BEFORE Str::markdown() processes it.
     *
     * Detects: JSON, XML/HTML payloads, stack traces, HTTP request/response,
     * SQL queries, key:value blocks, and generic code patterns.
     */
    public static function autoDetectCodeBlocks(string $text): string
    {
        // Don't process if user already used markdown code fences.
        if (preg_match('/^```/m', $text)) {
            return $text;
        }

        $lines = explode("\n", $text);
        $result = [];
        $codeBuffer = [];
        $codeLabel = null;
        $inCode = false;

        foreach ($lines as $line) {
            $detected = self::detectCodeLine($line);

            if ($detected) {
                if (! $inCode) {
                    $inCode = true;
                    $codeLabel = $detected['label'];
                }
                $codeBuffer[] = $line;
            } else {
                if ($inCode) {
                    // Flush code buffer.
                    $result[] = self::wrapCodeBlock($codeBuffer, $codeLabel);
                    $codeBuffer = [];
                    $codeLabel = null;
                    $inCode = false;
                }
                $result[] = $line;
            }
        }

        // Flush remaining buffer.
        if (! empty($codeBuffer)) {
            $result[] = self::wrapCodeBlock($codeBuffer, $codeLabel);
        }

        return implode("\n", $result);
    }

    /**
     * Check if a line looks like code/payload content.
     * Returns ['label' => '...'] if detected, null otherwise.
     */
    protected static function detectCodeLine(string $line): ?array
    {
        $trimmed = trim($line);

        if ($trimmed === '') {
            return null;
        }

        // JSON patterns: { }, [ ], "key": value
        if (preg_match('/^\s*[\{\[\]\}]/', $trimmed) || preg_match('/^\s*"[^"]+"\s*:/', $trimmed)) {
            return ['label' => 'JSON'];
        }

        // XML/HTML tags as payload
        if (preg_match('/^\s*<\/?[a-zA-Z][\w\-]*[\s>\/]/', $trimmed) && ! preg_match('/^<(p|br|div|span|a|strong|em|ul|ol|li|h[1-6]|img|table|tr|td|th|thead|tbody)\b/i', $trimmed)) {
            return ['label' => 'XML'];
        }

        // HTTP request/response lines
        if (preg_match('/^(GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS)\s+\S+\s+HTTP/i', $trimmed)) {
            return ['label' => 'HTTP Request'];
        }
        if (preg_match('/^HTTP\/[\d\.]+\s+\d{3}/', $trimmed)) {
            return ['label' => 'HTTP Response'];
        }
        // HTTP headers: Key: Value
        if (preg_match('/^(Content-Type|Authorization|Accept|Host|User-Agent|X-[\w\-]+|Cache-Control|Cookie|Set-Cookie)\s*:/i', $trimmed)) {
            return ['label' => 'HTTP Headers'];
        }

        // Stack traces / error patterns
        if (preg_match('/^\s*(#\d+\s|at\s+\S+\.php|at\s+\S+\.java|at\s+\S+\.js|at\s+\S+\.ts)/', $trimmed)) {
            return ['label' => 'Stack Trace'];
        }
        if (preg_match('/^(Exception|Error|Fatal|Traceback|Caused by|TypeError|ReferenceError|RuntimeException|BadMethodCallException|Illuminate\\\\)/i', $trimmed)) {
            return ['label' => 'Error'];
        }
        if (preg_match('/^\s*in\s+\S+\.php\s*(on\s+line\s+\d+|:\d+)/', $trimmed)) {
            return ['label' => 'Error'];
        }

        // SQL queries
        if (preg_match('/^\s*(SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP|SHOW|DESCRIBE|EXPLAIN)\s+/i', $trimmed)) {
            return ['label' => 'SQL'];
        }

        // cURL commands
        if (preg_match('/^\s*curl\s+/i', $trimmed)) {
            return ['label' => 'cURL'];
        }

        // Log lines with timestamps [2026-01-01 12:00:00]
        if (preg_match('/^\[\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}:\d{2}/', $trimmed)) {
            return ['label' => 'Log'];
        }

        return null;
    }

    protected static function wrapCodeBlock(array $lines, ?string $label): string
    {
        // Only wrap if we have at least 1 meaningful line.
        $content = implode("\n", $lines);
        $langHint = match ($label) {
            'JSON' => 'json',
            'SQL' => 'sql',
            'XML' => 'xml',
            'HTTP Request', 'HTTP Response', 'HTTP Headers' => 'http',
            default => '',
        };

        return "```{$langHint}\n{$content}\n```";
    }
}

<?php

namespace App\Helpers;

class CodeBlockHelper
{
    /**
     * Auto-detect code-like content in plain text and wrap it in markdown
     * code blocks BEFORE Str::markdown() processes it.
     *
     * Detects: JSON, XML/HTML payloads, stack traces, HTTP request/response,
     * SQL queries, cURL commands, and log lines.
     *
     * Also handles mixed lines like "ini payloadnya {" by splitting prose
     * from the code portion.
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
        $braceDepth = 0;
        $bracketDepth = 0;

        foreach ($lines as $line) {
            if ($inCode) {
                // Track brace/bracket depth to know when block ends.
                $braceDepth += substr_count($line, '{') - substr_count($line, '}');
                $bracketDepth += substr_count($line, '[') - substr_count($line, ']');
                $codeBuffer[] = $line;

                // Block ended: depth back to 0 or below.
                if ($braceDepth <= 0 && $bracketDepth <= 0) {
                    $result[] = self::wrapCodeBlock($codeBuffer, $codeLabel);
                    $codeBuffer = [];
                    $codeLabel = null;
                    $inCode = false;
                    $braceDepth = 0;
                    $bracketDepth = 0;
                }
                continue;
            }

            // Check if this line is entirely a code line.
            $detected = self::detectCodeLine($line);
            if ($detected) {
                $codeLabel = $detected['label'];
                $codeBuffer[] = $line;
                $inCode = true;
                $braceDepth = substr_count($line, '{') - substr_count($line, '}');
                $bracketDepth = substr_count($line, '[') - substr_count($line, ']');

                // If brace/bracket is already balanced (single-line), flush immediately.
                if ($braceDepth <= 0 && $bracketDepth <= 0) {
                    $result[] = self::wrapCodeBlock($codeBuffer, $codeLabel);
                    $codeBuffer = [];
                    $codeLabel = null;
                    $inCode = false;
                    $braceDepth = 0;
                    $bracketDepth = 0;
                }
                continue;
            }

            // Check if line ENDS with { or [ (mixed: prose + start of code block).
            // e.g. "ini log payloadnya {"
            if (preg_match('/^(.+?)\s*(\{)\s*$/', $line, $mixMatch)) {
                // Emit the prose part.
                $result[] = $mixMatch[1];
                // Start code block from {
                $codeBuffer[] = $mixMatch[2];
                $codeLabel = 'JSON';
                $inCode = true;
                $braceDepth = 1;
                $bracketDepth = 0;
                continue;
            }
            if (preg_match('/^(.+?)\s*(\[)\s*$/', $line, $mixMatch)) {
                $result[] = $mixMatch[1];
                $codeBuffer[] = $mixMatch[2];
                $codeLabel = 'JSON';
                $inCode = true;
                $braceDepth = 0;
                $bracketDepth = 1;
                continue;
            }

            $result[] = $line;
        }

        // Flush remaining code buffer (unclosed block).
        if (! empty($codeBuffer)) {
            $result[] = self::wrapCodeBlock($codeBuffer, $codeLabel);
        }

        return implode("\n", $result);
    }

    /**
     * Check if a line looks like code/payload content (starts with code pattern).
     * Returns ['label' => '...'] if detected, null otherwise.
     */
    protected static function detectCodeLine(string $line): ?array
    {
        $trimmed = trim($line);

        if ($trimmed === '') {
            return null;
        }

        // JSON: line starts with { } [ ] or "key": value
        if (preg_match('/^\s*[\{\}\[\]]/', $trimmed)) {
            return ['label' => 'JSON'];
        }
        if (preg_match('/^\s*"[^"]+"\s*:/', $trimmed)) {
            return ['label' => 'JSON'];
        }

        // XML/HTML tags as payload (not common HTML elements)
        if (preg_match('/^\s*<\/?[a-zA-Z][\w\-]*[\s>\/]/', $trimmed)
            && ! preg_match('/^<(p|br|div|span|a|strong|em|ul|ol|li|h[1-6]|img|table|tr|td|th|thead|tbody)\b/i', $trimmed)) {
            return ['label' => 'XML'];
        }

        // HTTP request/response lines
        if (preg_match('/^(GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS)\s+\S+\s+HTTP/i', $trimmed)) {
            return ['label' => 'HTTP Request'];
        }
        if (preg_match('/^HTTP\/[\d\.]+\s+\d{3}/', $trimmed)) {
            return ['label' => 'HTTP Response'];
        }
        if (preg_match('/^(Content-Type|Authorization|Accept|Host|User-Agent|X-[\w\-]+|Cache-Control|Cookie|Set-Cookie)\s*:/i', $trimmed)) {
            return ['label' => 'HTTP Headers'];
        }

        // Stack traces / error patterns
        if (preg_match('/^\s*(#\d+\s|at\s+\S+\.(php|java|js|ts|py))/', $trimmed)) {
            return ['label' => 'Stack Trace'];
        }
        if (preg_match('/^(Exception|Error|Fatal|Traceback|Caused by|TypeError|ReferenceError|RuntimeException|BadMethodCallException|Illuminate\\\\)/i', $trimmed)) {
            return ['label' => 'Error'];
        }
        if (preg_match('/^\s*in\s+\S+\.php\s*(on\s+line\s+\d+|:\d+)/', $trimmed)) {
            return ['label' => 'Error'];
        }

        // SQL queries
        if (preg_match('/^\s*(SELECT|INSERT\s+INTO|UPDATE|DELETE\s+FROM|CREATE|ALTER|DROP|SHOW|DESCRIBE|EXPLAIN)\s+/i', $trimmed)) {
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

    /**
     * Auto-link bare URLs in rendered HTML that are not already inside <a> or <code> tags.
     * Call this AFTER Str::markdown() processing.
     */
    public static function autoLinkUrls(string $html): string
    {
        // Split HTML into tags and text nodes to avoid linking inside <a>, <code>, <pre>.
        $parts = preg_split('/(<[^>]+>)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $insideA = 0;
        $insidePre = 0;
        $insideCode = 0;

        foreach ($parts as &$part) {
            // Track opening/closing tags.
            if (preg_match('/^<(a|\/a|pre|\/pre|code|\/code)\b/i', $part, $tag)) {
                $t = strtolower($tag[1]);
                if ($t === 'a') $insideA++;
                elseif ($t === '/a') $insideA = max(0, $insideA - 1);
                elseif ($t === 'pre') $insidePre++;
                elseif ($t === '/pre') $insidePre = max(0, $insidePre - 1);
                elseif ($t === 'code') $insideCode++;
                elseif ($t === '/code') $insideCode = max(0, $insideCode - 1);
                continue;
            }

            // Skip if inside tag or non-text.
            if (str_starts_with($part, '<') || $insideA > 0 || $insidePre > 0 || $insideCode > 0) {
                continue;
            }

            // Replace bare URLs in text nodes.
            $part = preg_replace_callback(
                '#(https?://[^\s<>\'")\]]+)#i',
                function ($matches) {
                    $url = $matches[1];
                    $trailing = '';
                    if (preg_match('/([.,;:!?\)]+)$/', $url, $punct)) {
                        $url = substr($url, 0, -strlen($punct[1]));
                        $trailing = $punct[1];
                    }
                    return '<a href="' . e($url) . '" target="_blank" rel="noopener" style="color:#60a5fa;text-decoration:underline;word-break:break-all;">' . e($url) . '</a>' . $trailing;
                },
                $part
            );
        }

        return implode('', $parts);
    }
}

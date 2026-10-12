<?php

namespace App\Support;

class DocsCodeHighlighter
{
    public static function curl(string $code): string
    {
        $lines = explode("\n", trim($code));
        $html = [];

        foreach ($lines as $line) {
            $html[] = self::highlightCurlLine($line);
        }

        return implode("\n", $html) ?: htmlspecialchars(trim($code), ENT_QUOTES, 'UTF-8');
    }

    public static function json(string $code): string
    {
        $trimmed = trim($code);
        if ($trimmed === '') {
            return '';
        }

        $decoded = json_decode($trimmed);

        if (json_last_error() === JSON_ERROR_NONE) {
            $trimmed = (string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return self::highlightJsonText($trimmed);
    }

    private static function highlightCurlLine(string $line): string
    {
        $escaped = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');

        $escaped = (string) preg_replace('/\bcurl\b/', '<span class="hl-cmd">curl</span>', $escaped);
        $escaped = (string) preg_replace('/\s(-[A-Za-z])\b/', ' <span class="hl-flag">$1</span>', $escaped);
        $escaped = (string) preg_replace('/\b(GET|POST|PUT|PATCH|DELETE)\b/', '<span class="hl-kw">$1</span>', $escaped);
        $escaped = (string) preg_replace('/&quot;(?:[^&]|&(?!quot;))*&quot;/', '<span class="hl-str">$0</span>', $escaped);

        return $escaped;
    }

    private static function highlightJsonText(string $json): string
    {
        $escaped = htmlspecialchars($json, ENT_QUOTES, 'UTF-8');

        $escaped = (string) preg_replace(
            '/(&quot;(?:[^&]|&(?!quot;))*&quot;)(\s*:)/',
            '<span class="hl-key">$1</span>$2',
            $escaped,
        );

        $escaped = (string) preg_replace_callback(
            '/(:\s*)(&quot;(?:[^&]|&(?!quot;))*&quot;)/',
            static fn (array $m): string => $m[1].'<span class="hl-str">'.$m[2].'</span>',
            $escaped,
        );

        $escaped = (string) preg_replace(
            '/(:\s*)(-?\d+(?:\.\d+)?)([\s,\n\r]|$)/',
            '$1<span class="hl-num">$2</span>$3',
            $escaped,
        );

        $escaped = (string) preg_replace(
            '/(:\s*)(true|false|null)([\s,\n\r]|$)/',
            '$1<span class="hl-kw">$2</span>$3',
            $escaped,
        );

        return $escaped;
    }
}

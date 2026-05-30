<?php

namespace App\Support;

class PageHtmlSanitizer
{
    public static function clean(string $html): string
    {
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<iframe\b[^>]*>.*?</iframe>#is', '', $html) ?? $html;
        $html = preg_replace('#<object\b[^>]*>.*?</object>#is', '', $html) ?? $html;
        $html = preg_replace('#<embed\b[^>]*>#is', '', $html) ?? $html;
        $html = preg_replace('# on\w+\s*=\s*"[^"]*"#i', '', $html) ?? $html;
        $html = preg_replace("# on\w+\s*=\s*'[^']*'#i", '', $html) ?? $html;
        $html = preg_replace('#javascript:#i', '', $html) ?? $html;

        return trim($html);
    }
}

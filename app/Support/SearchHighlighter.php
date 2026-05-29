<?php

namespace App\Support;

class SearchHighlighter
{
    public static function highlight(?string $text, string $query): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $escaped = e($text);
        $terms = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($terms === []) {
            return $escaped;
        }

        $parts = array_map(static fn (string $term): string => preg_quote($term, '/'), $terms);
        $pattern = '/(' . implode('|', $parts) . ')/iu';

        return (string) preg_replace(
            $pattern,
            '<mark class="articles-search-hit">$1</mark>',
            $escaped,
        );
    }

    /**
     * Highlight search terms in HTML body text (between tags only).
     */
    public static function highlightHtml(?string $html, string $query): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $terms = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($terms === []) {
            return $html;
        }

        $parts = array_map(static fn (string $term): string => preg_quote($term, '/'), $terms);
        $termPattern = '/(' . implode('|', $parts) . ')/iu';

        return (string) preg_replace_callback(
            '/>([^<]+)</u',
            static function (array $matches) use ($termPattern): string {
                $text = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
                $highlighted = (string) preg_replace(
                    $termPattern,
                    '<mark class="articles-search-hit">$1</mark>',
                    $text,
                );

                return '>' . $highlighted . '<';
            },
            $html,
        );
    }
}

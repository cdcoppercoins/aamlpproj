<?php

namespace App\Support;

/**
 * Live site serves files from ../public_html (not laravel/public).
 * Local dev uses laravel/public via artisan serve.
 */
class WebPublicPath
{
    public static function root(): string
    {
        static $root = null;

        if ($root !== null) {
            return $root;
        }

        $sibling = dirname(base_path()) . DIRECTORY_SEPARATOR . 'public_html';

        if (is_dir($sibling) && is_file($sibling . DIRECTORY_SEPARATOR . 'index.php')) {
            $root = $sibling;
        } else {
            $root = public_path();
        }

        return $root;
    }

    public static function path(string $relativePath = ''): string
    {
        $relativePath = trim($relativePath, '/');

        if ($relativePath === '') {
            return self::root();
        }

        return self::root() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
}

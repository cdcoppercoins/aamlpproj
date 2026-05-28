<?php
/**
 * One-time: verify collection Blade on the server has the username fix.
 * Upload to public_html/view-check.php, open in browser, then DELETE this file.
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$home = dirname(__DIR__);
$roots = array_values(array_unique([
    $home . '/laravel',
    $home . '/new.minilicenseplates.com',
    '/home/minilp/laravel',
]));

$rel = 'resources/views/collection/index.blade.php';

echo "Collection index Blade check\n";
echo "Looking for: $rel\n\n";

foreach ($roots as $root) {
    echo "========== $root ==========\n";
    $path = $root . '/' . $rel;
    if (! is_file($path)) {
        echo "File: NOT FOUND\n\n";
        continue;
    }

    $mtime = date('Y-m-d H:i:s', (int) filemtime($path));
    $size = filesize($path);
    echo "File: OK (modified $mtime, {$size} bytes)\n";

    $lines = file($path) ?: [];
    $snippet = '';
    foreach ($lines as $num => $line) {
        if (str_contains($line, 'collection-member-username')) {
            $snippet = 'Line ' . ($num + 1) . ': ' . trim($line);
            break;
        }
    }

    if ($snippet === '') {
        echo "Username line: NOT FOUND in file\n";
    } else {
        echo $snippet . "\n";
    }

    $raw = file_get_contents($path) ?: '';
    if (str_contains($raw, '@{{ $collector->username }}')) {
        echo "Status: BROKEN — still has @{{ (Blade escape). Re-upload laravel/resources/views/collection/ from a fresh packager run.\n";
    } elseif (str_contains($raw, '{{ $collector->username }}')) {
        echo "Status: OK — Blade will render the username.\n";
    } else {
        echo "Status: UNKNOWN — could not match expected patterns.\n";
    }

    $viewsCache = $root . '/storage/framework/views';
    if (is_dir($viewsCache)) {
        $php = glob($viewsCache . '/*.php') ?: [];
        echo 'Compiled views cache: ' . count($php) . " .php file(s)\n";
        if (count($php) > 0) {
            echo "Action: delete all .php files in storage/framework/views/ (FileZilla), then Ctrl+F5 the page.\n";
        } else {
            echo "Compiled views cache: empty (good after upload).\n";
        }
    }

    echo "\n";
}

echo "Done. Delete view-check.php from public_html when finished.\n";

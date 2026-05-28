<?php
/**
 * Plain-English check: why usernames still look wrong on the live site.
 * Upload to public_html/username-fix-check.php → open in browser → DELETE when done.
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$home = dirname(__DIR__);
$indexFile = __DIR__ . '/index.php';
$bladeRel = 'resources/views/collection/index.blade.php';

echo "USERNAME FIX — LIVE SITE CHECK\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// 1) Which Laravel folder does the live site actually use?
echo "1) WHICH FOLDER POWERS THE WEBSITE?\n";
$laravelFromIndex = null;
if (is_file($indexFile)) {
    $indexText = file_get_contents($indexFile) ?: '';
    if (preg_match("/dirname\(__DIR__\)\s*\.\s*['\"]\/([^'\"]+)['\"]/", $indexText, $m)) {
        $laravelFromIndex = $home . '/' . $m[1];
    } elseif (str_contains($indexText, "'/laravel'") || str_contains($indexText, '"/laravel"')) {
        $laravelFromIndex = $home . '/laravel';
    } elseif (str_contains($indexText, 'new.minilicenseplates.com')) {
        $laravelFromIndex = $home . '/new.minilicenseplates.com';
    }
    echo "   public_html/index.php found: yes\n";
    echo "   index.php points at: " . ($laravelFromIndex ?? '(could not parse — see first lines below)') . "\n";
    $lines = file($indexFile) ?: [];
    echo "   First lines of index.php:\n";
    foreach (array_slice($lines, 0, 12) as $line) {
        echo '   | ' . rtrim($line, "\r\n") . "\n";
    }
} else {
    echo "   public_html/index.php: MISSING\n";
}

$candidates = array_values(array_unique(array_filter([
    $laravelFromIndex,
    $home . '/laravel',
    $home . '/new.minilicenseplates.com',
    '/home/minilp/laravel',
    '/home/minilp/new.minilicenseplates.com',
])));

echo "\n2) USERNAME TEMPLATE IN EACH FOLDER\n";
$goodPath = null;
foreach ($candidates as $root) {
    echo "   --- $root ---\n";
    if (! is_dir($root)) {
        echo "   Folder: does not exist\n\n";
        continue;
    }
    $blade = $root . '/' . $bladeRel;
    if (! is_file($blade)) {
        echo "   Template file: MISSING\n\n";
        continue;
    }
    echo '   Template file: yes (changed ' . date('Y-m-d H:i:s', (int) filemtime($blade)) . ")\n";
    $raw = file_get_contents($blade) ?: '';
    foreach (explode("\n", $raw) as $num => $line) {
        if (str_contains($line, 'collection-member-username')) {
            echo '   Key line ' . ($num + 1) . ': ' . trim($line) . "\n";
            break;
        }
    }
    if (str_contains($raw, '@{{ $collector->username }}')) {
        echo "   Status: WRONG — old typo still here (shows {{ username }} on the page)\n";
    } elseif (str_contains($raw, '{{ $collector->username }}')) {
        echo "   Status: CORRECT — should show the real username\n";
        if ($root === $laravelFromIndex || $goodPath === null) {
            $goodPath = $root;
        }
    } else {
        echo "   Status: unknown pattern\n";
    }
    echo "\n";
}

$activeRoot = $laravelFromIndex && is_dir($laravelFromIndex) ? $laravelFromIndex : $goodPath;
echo "3) SUMMARY (read this)\n";
if ($laravelFromIndex && is_dir($home . '/new.minilicenseplates.com') && is_dir($home . '/laravel')) {
    echo "   WARNING: BOTH 'laravel' and 'new.minilicenseplates.com' exist.\n";
    echo "   You must upload the fix to the folder index.php uses (see section 1).\n";
}
if ($activeRoot) {
    $blade = $activeRoot . '/' . $bladeRel;
    $raw = is_file($blade) ? (file_get_contents($blade) ?: '') : '';
    if (str_contains($raw, '@{{ $collector->username }}')) {
        echo "   The live site is using a folder that still has the OLD template.\n";
        echo "   Fix: upload ONE file from your PC to the server:\n";
        echo "   PC:  d:\\aamlpproj\\resources\\views\\collection\\index.blade.php\n";
        echo "   Server: $bladeRel\n";
        echo "   (inside the folder from section 1: $activeRoot)\n";
    } elseif (str_contains($raw, '{{ $collector->username }}')) {
        echo "   Template file looks CORRECT in the folder the site uses.\n";
        echo "   If the page still looks wrong, clear stale cache (section 4).\n";
    }
} else {
    echo "   Could not determine the active Laravel folder. Copy this whole page to support.\n";
}

echo "\n4) CLEAR STALE PAGE MEMORY (cache)\n";
if ($activeRoot) {
    $cleared = 0;
    foreach (['storage/framework/views', 'bootstrap/cache'] as $sub) {
        $dir = $activeRoot . '/' . $sub;
        if (! is_dir($dir)) {
            echo "   $sub: folder not found\n";
            continue;
        }
        $files = glob($dir . '/*.php') ?: [];
        $ok = 0;
        foreach ($files as $f) {
            if (@unlink($f)) {
                $ok++;
            }
        }
        $cleared += $ok;
        echo "   $sub: deleted $ok of " . count($files) . " .php file(s)\n";
    }
    if ($cleared > 0) {
        echo "   Done. Open My Collection on the site and press Ctrl+F5.\n";
    } else {
        echo "   No .php cache files removed (maybe already empty, or folder not writable).\n";
        echo "   In FileZilla, delete .php files in those two folders manually.\n";
    }
} else {
    echo "   Skipped — active folder unknown.\n";
}

echo "\n5) DELETE THIS FILE\n";
echo "   Remove username-fix-check.php from public_html when finished.\n";

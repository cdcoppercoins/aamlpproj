<?php
/**
 * Finish deploy after FTP upload (migrations + clear caches).
 * Upload to public_html/apply-deploy.php
 */
declare(strict_types=1);

set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');

$token = (string) ($_GET['token'] ?? '');
if ($token === '') {
    http_response_code(400);
    echo "Missing token.\n";
    exit(1);
}

$laravelRoot = dirname(__DIR__) . '/laravel';
$envPath = $laravelRoot . '/.env';

if (! is_file($envPath)) {
    http_response_code(500);
    echo "laravel/.env not found.\n";
    exit(1);
}

$expected = null;
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }
    if (str_starts_with($line, 'DEPLOY_APPLY_TOKEN=')) {
        $expected = trim(substr($line, 19), " \t\"'");
        break;
    }
}

if ($expected === null || $expected === '') {
    http_response_code(500);
    echo "DEPLOY_APPLY_TOKEN is not set in laravel/.env on the server.\n";
    exit(1);
}

if (! hash_equals($expected, $token)) {
    http_response_code(403);
    echo "Invalid token.\n";
    exit(1);
}

$home = dirname($laravelRoot);
$publicHtmlRoot = $home . '/public_html';
$extractLog = $home . '/deploy-extract.log';
$extractLock = $home . '/deploy-extract.lock';

echo "apply-deploy.php version: 2026-06-03f\n";

$releaseZipPath = $home . '/release.zip';
if (! is_file($releaseZipPath) && is_file($publicHtmlRoot . '/release.zip')) {
    $releaseZipPath = $publicHtmlRoot . '/release.zip';
    echo "release.zip: found in public_html/\n";
} elseif (is_file($releaseZipPath)) {
    echo 'release.zip: ' . filesize($releaseZipPath) . " bytes at {$releaseZipPath}\n";
} else {
    echo "release.zip: not on server (already extracted or not uploaded)\n";
}
echo "\n";

function deploy_find_unzip(): ?string
{
    foreach (['/usr/bin/unzip', '/bin/unzip'] as $candidate) {
        if (is_executable($candidate)) {
            return $candidate;
        }
    }
    $out = [];
    exec('command -v unzip 2>/dev/null', $out, $code);
    if ($code === 0 && isset($out[0]) && $out[0] !== '') {
        return trim($out[0]);
    }

    return null;
}

function deploy_start_background_extract(string $zipPath, string $home, string $log, string $lock): bool
{
    $unzipBin = deploy_find_unzip();
    if ($unzipBin === null) {
        echo "unzip not available. Use cPanel File Manager: select release.zip -> Extract.\n";
        return false;
    }

    echo "EXTRACT_STARTED\n";
    if (function_exists('flush')) {
        flush();
    }

    file_put_contents($lock, (string) time());
    $script = 'cd ' . escapeshellarg($home)
        . ' && ' . escapeshellarg($unzipBin) . ' -oq ' . escapeshellarg($zipPath)
        . ' >> ' . escapeshellarg($log) . ' 2>&1'
        . '; rm -f ' . escapeshellarg($zipPath)
        . '; rm -f ' . escapeshellarg($lock);

    if (function_exists('proc_close') && function_exists('proc_open')) {
        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open('sh -c ' . escapeshellarg($script . ' &'), $descriptors, $pipes);
        if (is_resource($process)) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }
    } else {
        exec('sh -c ' . escapeshellarg($script . ' &'));
    }

    echo "Unzip is running on the server (about 2-3 minutes). The finish bat will wait and retry.\n\n";

    return true;
}

if (is_file($releaseZipPath)) {
    if (is_file($extractLock)) {
        $lockAge = time() - (int) trim((string) file_get_contents($extractLock));
        if ($lockAge < 900) {
            echo "EXTRACT_IN_PROGRESS ({$lockAge}s)\n\n";
            exit(0);
        }
        @unlink($extractLock);
    }

    echo "==> Starting background unzip into {$home}\n";
    if (! deploy_start_background_extract($releaseZipPath, $home, $extractLog, $extractLock)) {
        http_response_code(500);
        echo "ZIP_EXTRACT_FAILED\n";
        exit(1);
    }
    exit(0);
}

if (is_file($extractLock)) {
    echo "EXTRACT_IN_PROGRESS\n\n";
    exit(0);
}

echo "ZIP_EXTRACT_OK\n\n";

$buildStamp = $laravelRoot . '/DEPLOY_BUILD.txt';
if (is_file($buildStamp)) {
    echo 'Live build: ' . trim((string) file_get_contents($buildStamp)) . "\n\n";
}

$artisan = $laravelRoot . '/artisan';
if (! is_file($artisan)) {
    http_response_code(500);
    echo "artisan not found.\n";
    exit(1);
}

$commands = [
    'view:clear',
    'cache:clear',
    'route:clear',
    'config:clear',
    'migrate --force',
];

echo 'Deploy apply started: ' . date('Y-m-d H:i:s') . "\n\n";

foreach ($commands as $command) {
    echo "==> php artisan {$command}\n";
    $cmd = 'cd ' . escapeshellarg($laravelRoot) . ' && php ' . escapeshellarg($artisan) . ' ' . $command . ' 2>&1';
    passthru($cmd, $exitCode);
    echo "\n";
    if ($exitCode !== 0) {
        echo "Command failed (exit {$exitCode}).\n";
        exit(1);
    }
}

$routesFile = $laravelRoot . '/routes/web.php';
if (is_file($routesFile)) {
    $routesText = file_get_contents($routesFile);
    echo (str_contains($routesText, 'collection.manage.update-row') ? 'Manage row save route: OK' : 'Manage row save route: MISSING') . "\n";
}

$manageBlade = $laravelRoot . '/resources/views/collection/manage.blade.php';
if (is_file($manageBlade)) {
    $manageText = file_get_contents($manageBlade);
    echo (str_contains($manageText, 'Save row') ? 'Manage Save row button: OK' : 'Manage Save row button: MISSING') . "\n";
}

$mainCss = $publicHtmlRoot . '/main.css';
if (is_file($mainCss)) {
    $cssText = file_get_contents($mainCss);
    echo (str_contains($cssText, 'collection-manage-row-save') ? 'main.css manage save styles: OK' : 'main.css manage save styles: MISSING') . "\n";
}

echo "\nDeploy apply finished.\n";

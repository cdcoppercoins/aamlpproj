<?php
/**
 * Finish deploy: clear compiled caches on server.
 * Lives in public_html/apply-deploy.php
 */
declare(strict_types=1);

set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');

echo "apply-deploy.php version: quick-4b\n";

try {
    $token = (string) ($_GET['token'] ?? '');
    if ($token === '') {
        throw new RuntimeException('Missing token.');
    }

    $laravelRoot = dirname(__DIR__) . '/laravel';
    echo "laravel root: {$laravelRoot}\n";

    if (! is_dir($laravelRoot)) {
        throw new RuntimeException('laravel/ folder not found.');
    }

    $envPath = $laravelRoot . '/.env';
    if (! is_file($envPath)) {
        throw new RuntimeException('laravel/.env not found. Run Deploy-Fix-Env.bat.');
    }

    $expected = null;
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (str_starts_with($line, 'DEPLOY_APPLY_TOKEN=')) {
            $expected = trim(substr($line, 19), " \t\"'");
            break;
        }
    }

    if ($expected === null || $expected === '' || ! hash_equals($expected, $token)) {
        throw new RuntimeException('Invalid or missing DEPLOY_APPLY_TOKEN in laravel/.env.');
    }

    echo "token: OK\n";

    $vendorOk = is_file($laravelRoot . '/vendor/autoload.php');
    echo 'vendor: ' . ($vendorOk ? 'OK' : 'MISSING — run Deploy-ToProduction.ps1 -IncludeVendor once') . "\n";

    $buildStamp = $laravelRoot . '/DEPLOY_BUILD.txt';
    if (is_file($buildStamp)) {
        echo 'Live build: ' . trim((string) file_get_contents($buildStamp)) . "\n";
    }

    $removed = 0;
    foreach (glob($laravelRoot . '/bootstrap/cache/*.php') ?: [] as $file) {
        if (is_file($file) && @unlink($file)) {
            $removed++;
        }
    }
    foreach (glob($laravelRoot . '/storage/framework/views/*.php') ?: [] as $file) {
        if (is_file($file) && @unlink($file)) {
            $removed++;
        }
    }
    echo "Cleared {$removed} compiled cache files.\n";

    if ($vendorOk && function_exists('exec')) {
        $artisan = $laravelRoot . '/artisan';
        $cmd = 'cd ' . escapeshellarg($laravelRoot) . ' && php ' . escapeshellarg($artisan) . ' migrate --force 2>&1';
        $migrateOut = [];
        $migrateCode = 1;
        exec($cmd, $migrateOut, $migrateCode);
        echo "==> migrate --force (exit {$migrateCode})\n";
        if ($migrateOut !== []) {
            echo implode("\n", $migrateOut) . "\n";
        }
        if ($migrateCode !== 0) {
            echo "WARN: migrate failed — fix DB_* in laravel/.env if the site shows database errors.\n";
        }
    } else {
        echo "migrate: skipped (host disables exec() from the browser — OK for normal code deploys).\n";
        echo "If you added new database migrations, run in cPanel Terminal:\n";
        echo "  cd /home/minilp/laravel && php artisan migrate --force\n";
    }

    echo "DEPLOY_APPLY_OK\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}

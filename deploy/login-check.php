<?php
/**
 * One-time login diagnostic. Upload to /public_html/login-check.php
 * Open https://minilicenseplates.com/login-check.php then DELETE this file.
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$home = dirname(__DIR__);
$laravelRoot = $home . '/laravel';

if (! is_file($laravelRoot . '/vendor/autoload.php')) {
    echo "ERROR: laravel folder not found at $laravelRoot\n";
    exit;
}

require $laravelRoot . '/vendor/autoload.php';
$app = require $laravelRoot . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Login diagnostic\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "Database: OK\n";
} catch (\Throwable $e) {
    echo "Database: FAILED - " . $e->getMessage() . "\n";
    exit;
}

$schema = \Illuminate\Support\Facades\Schema::class;

echo 'users table: ' . ($schema::hasTable('users') ? 'yes' : 'NO') . "\n";
if ($schema::hasTable('users')) {
    $cols = $schema::getColumnListing('users');
    echo '  username column: ' . (in_array('username', $cols, true) ? 'OK' : 'MISSING - run migrations') . "\n";
    echo '  is_blocked column: ' . (in_array('is_blocked', $cols, true) ? 'OK' : 'MISSING - run migrations') . "\n";
    echo '  member accounts on server: ' . \Illuminate\Support\Facades\DB::table('users')->count() . "\n";
}

echo 'sessions table: ' . ($schema::hasTable('sessions') ? 'yes' : 'NO - login cannot stick') . "\n";
echo 'SESSION_DRIVER (.env): ' . config('session.driver') . "\n";
echo 'APP_URL: ' . config('app.url') . "\n";

$sessionPath = $laravelRoot . '/storage/framework/sessions';
if (config('session.driver') === 'file') {
    echo 'Session folder writable: ' . (is_writable($sessionPath) ? 'yes' : 'NO') . "\n";
}

echo "\nIf member accounts is 0, create an account at /register on the LIVE site.\n";
echo "If username column is MISSING, ask host to run: php /home/minilp/laravel/artisan migrate --force\n";
echo "\nDelete login-check.php from public_html when done.\n";

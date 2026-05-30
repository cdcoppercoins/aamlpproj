<?php
/**
 * One-time production diagnostic. Upload to /public_html/site-check.php
 * Open https://minilicenseplates.com/site-check.php then DELETE this file.
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$home = dirname(__DIR__);
$roots = array_values(array_unique([
    $home . '/laravel',
    $home . '/new.minilicenseplates.com',
    '/home/minilp/laravel',
]));

echo "MiniLicensePlates.com site check\n";
echo "Generated checks at: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($roots as $root) {
    echo "========== $root ==========\n";
    if (! is_dir($root)) {
        echo "Folder: NOT FOUND\n\n";
        continue;
    }

    $checks = [
        'vendor/autoload.php' => "$root/vendor/autoload.php",
        'bootstrap/app.php' => "$root/bootstrap/app.php",
        'app/Http/Controllers/HomeController.php' => "$root/app/Http/Controllers/HomeController.php",
        '.env' => "$root/.env",
    ];
    foreach ($checks as $label => $path) {
        echo "$label: " . (is_file($path) ? 'OK' : 'MISSING') . "\n";
    }

    if (! is_file("$root/vendor/autoload.php") || ! is_file("$root/bootstrap/app.php")) {
        echo "\n";
        continue;
    }

    try {
        require "$root/vendor/autoload.php";
        /** @var \Illuminate\Foundation\Application $app */
        $app = require "$root/bootstrap/app.php";
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        echo "Laravel bootstrap: OK\n";
        echo 'APP_ENV: ' . config('app.env') . "\n";

        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "Database connection: OK\n";

        if (\Illuminate\Support\Facades\Schema::hasTable('hero_slides')) {
            $cols = \Illuminate\Support\Facades\Schema::getColumnListing('hero_slides');
            echo 'hero_slides columns: ' . implode(', ', $cols) . "\n";
            echo 'fill_slide column: ' . (in_array('fill_slide', $cols, true) ? 'OK' : 'MISSING (migrations needed)') . "\n";
        } else {
            echo "hero_slides table: not present (OK - uses config fallback)\n";
        }

        echo 'pages table: ' . (\Illuminate\Support\Facades\Schema::hasTable('pages') ? 'OK' : 'MISSING (run php artisan migrate)') . "\n";
        echo 'site_links table: ' . (\Illuminate\Support\Facades\Schema::hasTable('site_links') ? 'OK' : 'MISSING (run php artisan migrate)') . "\n";
        if (\Illuminate\Support\Facades\Schema::hasTable('site_links')) {
            $linkCols = \Illuminate\Support\Facades\Schema::getColumnListing('site_links');
            echo 'site_links url column: ' . (in_array('url', $linkCols, true) ? 'OK' : 'MISSING (run php artisan migrate)') . "\n";
        }

        $request = \Illuminate\Http\Request::create('/', 'GET');
        $response = $app->handle($request);
        echo 'Home page test status: ' . $response->getStatusCode() . "\n";
        if ($response->getStatusCode() >= 400) {
            echo "Response snippet:\n" . substr((string) $response->getContent(), 0, 800) . "\n";
        }
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo 'At: ' . $e->getFile() . ':' . $e->getLine() . "\n";
    }

    echo "\n";
}

$index = __DIR__ . '/index.php';
echo "========== public_html/index.php ==========\n";
if (! is_file($index)) {
    echo "MISSING - the website needs index.php in public_html\n";
} else {
    $lines = file($index) ?: [];
    echo "First 25 lines:\n";
    echo implode('', array_slice($lines, 0, 25));
}

echo "\nDone. Delete site-check.php from the server when finished.\n";

<?php
/**
 * One-time: diagnose /collection 500 errors.
 * Upload to public_html/collection-check.php, open in browser while signed in optional,
 * then DELETE this file.
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$laravelRoot = dirname(__DIR__) . '/laravel';

echo "Collection route diagnostic\n";
echo 'Time: ' . date('Y-m-d H:i:s') . "\n\n";

if (! is_file($laravelRoot . '/vendor/autoload.php')) {
    echo "ERROR: laravel/vendor/autoload.php not found\n";
    exit(1);
}

require $laravelRoot . '/vendor/autoload.php';
$app = require $laravelRoot . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$checks = [
    'collection_items' => Schema::hasTable('collection_items'),
    'collection_owned_items' => Schema::hasTable('collection_owned_items'),
    'collection_pieces (old name)' => Schema::hasTable('collection_pieces'),
    'collection_serial_sequences' => Schema::hasTable('collection_serial_sequences'),
    'collection_set_settings' => Schema::hasTable('collection_set_settings'),
];

echo "Tables:\n";
foreach ($checks as $label => $ok) {
    echo '  ' . $label . ': ' . ($ok ? 'OK' : 'MISSING') . "\n";
}

echo "\nModels / classes:\n";
foreach ([
    'App\\Models\\CollectionItem',
    'App\\Models\\CollectionOwnedItem',
    'App\\Support\\CollectionSerialAssigner',
] as $class) {
    echo '  ' . $class . ': ' . (class_exists($class) ? 'OK' : 'MISSING') . "\n";
}

if (Schema::hasTable('collection_items')) {
    $cols = Schema::getColumnListing('collection_items');
    echo "\ncollection_items still has quantity column: " . (in_array('quantity', $cols, true) ? 'YES (old schema — migrations incomplete)' : 'no') . "\n";
}

echo "\nPending migrations (2026_06):\n";
try {
    $ran = DB::table('migrations')->where('migration', 'like', '2026_06%')->pluck('migration')->all();
    if ($ran === []) {
        echo "  None recorded — 2026_06 migrations may never have run.\n";
    } else {
        foreach ($ran as $name) {
            echo "  Ran: $name\n";
        }
    }
} catch (\Throwable $e) {
    echo '  ERROR: ' . $e->getMessage() . "\n";
}

echo "\nSimulated /collection SQL (first query):\n";
try {
    $row = DB::table('collection_items')
        ->join('plates', 'plates.id', '=', 'collection_items.plate_id')
        ->leftJoin('collection_owned_items', 'collection_owned_items.collection_item_id', '=', 'collection_items.id')
        ->groupBy('plates.set_code')
        ->select(
            'plates.set_code',
            DB::raw('COUNT(DISTINCT collection_items.id) as entry_count')
        )
        ->limit(1)
        ->first();
    echo '  OK — query ran' . ($row ? " (sample set_code: {$row->set_code})" : ' (no collection data)') . "\n";
} catch (\Throwable $e) {
    echo '  ERROR: ' . $e->getMessage() . "\n";
}

echo "\nHome /collection route test:\n";
try {
    $request = \Illuminate\Http\Request::create('/collection', 'GET');
    $response = $app->handle($request);
    echo '  HTTP status: ' . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() >= 400) {
        echo substr((string) $response->getContent(), 0, 1200) . "\n";
    }
} catch (\Throwable $e) {
    echo '  ERROR: ' . $e->getMessage() . "\n";
    echo '  At: ' . $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\nCollection reports deploy check:\n";

$reportFiles = [
    'routes/web.php' => 'collection.reports.index',
    'app/Http/Controllers/CollectionController.php' => 'function reportsIndex',
    'resources/views/collection/index.blade.php' => 'collection.reports.index',
    'resources/views/collection/reports/index.blade.php' => 'Collection reports',
    'resources/views/components/collection-reports-sort-script.blade.php' => 'data-sortable-report',
];

foreach ($reportFiles as $rel => $needle) {
    $path = $laravelRoot . '/' . $rel;
    if (! is_file($path)) {
        echo "  MISSING file: $rel\n";
        continue;
    }
    $raw = file_get_contents($path) ?: '';
    $ok = str_contains($raw, $needle);
    echo '  ' . $rel . ': ' . ($ok ? 'OK' : "FOUND but missing “{$needle}” — re-upload") . "\n";
}

$reportsDir = $laravelRoot . '/resources/views/collection/reports';
if (is_dir($reportsDir)) {
    $views = glob($reportsDir . '/*.blade.php') ?: [];
    echo '  reports views: ' . count($views) . " file(s)\n";
} else {
    echo "  MISSING directory: resources/views/collection/reports/\n";
}

$cssPath = dirname(__DIR__) . '/public_html/main.css';
if (is_file($cssPath)) {
    $css = file_get_contents($cssPath) ?: '';
    echo '  public_html/main.css has report styles: ' . (str_contains($css, 'collection-reports-accordion') ? 'OK' : 'NO — re-upload main.css') . "\n";
} else {
    echo "  public_html/main.css: NOT FOUND\n";
}

echo "\n/collection/reports route test:\n";
try {
    $request = \Illuminate\Http\Request::create('/collection/reports', 'GET');
    $response = $app->handle($request);
    echo '  HTTP status: ' . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() >= 400) {
        echo substr((string) $response->getContent(), 0, 1200) . "\n";
    } elseif (! str_contains((string) $response->getContent(), 'Collection reports')) {
        echo "  WARNING: 200 response but page text looks wrong (view cache or old template).\n";
    }
} catch (\Throwable $e) {
    echo '  ERROR: ' . $e->getMessage() . "\n";
    echo '  At: ' . $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\nDone. Delete collection-check.php from public_html when finished.\n";

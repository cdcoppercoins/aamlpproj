<?php

namespace App\Console\Commands;

use App\Support\ImageOptimizer;
use App\Support\WebPublicPath;
use Illuminate\Console\Command;

class OptimizePublicImages extends Command
{
    protected $signature = 'images:optimize
                            {target=plates : plates, articles-media, history-media, hero, or all}
                            {--set= : For plates only — one set folder, e.g. m88p}
                            {--dry-run : Show what would run, without changing files}';

    protected $description = 'Resize and compress site images (default: all plate photos)';

    /** @var array<string, string> */
    private const OTHER_FOLDERS = [
        'articles-media' => 'article',
        'history-media' => 'history',
        'hero' => 'hero',
    ];

    public function handle(): int
    {
        if (! extension_loaded('gd')) {
            $this->error('PHP GD is not enabled on this server. Ask your host to enable the GD extension.');

            return 1;
        }

        $target = (string) $this->argument('target');

        if ($this->option('dry-run')) {
            return $this->dryRun($target);
        }

        if ($target === 'plates') {
            $setCode = $this->option('set');
            $path = WebPublicPath::path($setCode ? 'plates/' . $setCode : 'plates');
            $this->line("Optimizing plate images in: {$path}");

            $count = ImageOptimizer::optimizePlates($setCode ? (string) $setCode : null);
            $this->info("Done. {$count} plate images optimized.");

            return 0;
        }

        if ($target === 'all') {
            $plateCount = ImageOptimizer::optimizePlates();
            $this->info("plates: {$plateCount} images optimized.");
            $total = $plateCount;

            foreach (self::OTHER_FOLDERS as $name => $profile) {
                $count = ImageOptimizer::optimizeDirectory($name, $profile);
                $this->info("{$name}: {$count} images optimized.");
                $total += $count;
            }

            $this->info("Done. {$total} images processed.");

            return 0;
        }

        if (! isset(self::OTHER_FOLDERS[$target])) {
            $this->error('Unknown target. Use: plates, articles-media, history-media, hero, or all');

            return 1;
        }

        $count = ImageOptimizer::optimizeDirectory($target, self::OTHER_FOLDERS[$target]);
        $this->info("Done. {$count} images optimized in {$target}.");

        return 0;
    }

    private function dryRun(string $target): int
    {
        $this->line('DRY RUN — no files will be changed.');
        $this->newLine();

        if ($target === 'plates' || $target === 'all') {
            $setCode = $this->option('set');
            $path = WebPublicPath::path($setCode ? 'plates/' . $setCode : 'plates');
            $count = ImageOptimizer::countPlates($setCode ? (string) $setCode : null);

            $this->info("Plates folder: {$path}");
            $this->info("Images found: {$count}");

            if ($count === 0) {
                $this->warn('No JPG/PNG/WebP files found there. Check the path or upload plates first.');
            } else {
                $this->line('To optimize them, run the same command WITHOUT --dry-run');
            }
        }

        if ($target === 'all') {
            foreach (self::OTHER_FOLDERS as $name => $profile) {
                $folderPath = WebPublicPath::path($name);
                $count = ImageOptimizer::countInDirectory($name);
                $this->info("{$name}: {$count} images at {$folderPath}");
            }
        } elseif (isset(self::OTHER_FOLDERS[$target])) {
            $folderPath = WebPublicPath::path($target);
            $count = ImageOptimizer::countInDirectory($target);
            $this->info("{$target}: {$count} images at {$folderPath}");
        } elseif ($target !== 'plates') {
            $this->error('Unknown target. Use: plates, articles-media, history-media, hero, or all');

            return 1;
        }

        return 0;
    }
}

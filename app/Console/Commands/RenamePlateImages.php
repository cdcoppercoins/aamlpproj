<?php

namespace App\Console\Commands;

use App\Support\WebPublicPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RenamePlateImages extends Command
{
    protected $signature = 'plates:rename-images
                            {--dry-run : Show planned renames without applying}
                            {--skip-folders : Do not rename set folders to match set_code}
                            {--folder= : Only process this subfolder under public/plates}';

    protected $description = 'Rename plate image files to {image_base}_a.{ext} using jurisdiction matching';

    /** @var array<string, string> */
    private array $filenameAliases = [
        'connectucut' => 'connecticut',
        'vitrginia' => 'virginia',
        'mass' => 'massachusetts',
        'guatemala' => 'guatamala',
        'pei' => 'princeedwardis',
        'districtcolumbia' => 'distofcolumbia',
        'districtofcolumbia' => 'distofcolumbia',
        'newhampshire' => 'newhampshire',
        'newjersey' => 'newjersey',
        'newmexico' => 'newmexico',
        'newyork' => 'newyork',
        'northcarolina' => 'northcarolina',
        'northdakota' => 'northdakota',
        'rhodeisland' => 'rhodeisland',
        'southcarolina' => 'southcarolina',
        'southdakota' => 'southdakota',
        'westvirginia' => 'westvirginia',
        'districtofcolumbia' => 'distofcolumbia',
        'britishcolumbia' => 'britishcolumbia',
        'britishguyana' => 'britishguyana',
        'britishhonduras' => 'britishhonduras',
        'puertorico' => 'puertorico',
        'virginislands' => 'virginislands',
        'princeedwardisland' => 'princeedwardis',
        'newbrunswick' => 'newbrunswick',
        'novascotia' => 'novascotia',
        'newfoundland' => 'newfoundland',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $skipFolders = (bool) $this->option('skip-folders');
        $platesRoot = WebPublicPath::path('plates');

        if (!is_dir($platesRoot)) {
            $this->error("Plates directory not found: {$platesRoot}");
            return 1;
        }

        if ($dryRun) {
            $this->warn('Dry run — no files or folders will be changed.');
        }

        $totalRenamed = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        $folderNames = array_map('basename', glob($platesRoot . '/*', GLOB_ONLYDIR) ?: []);
        sort($folderNames);

        $folderFilter = $this->option('folder');

        foreach ($folderNames as $folderName) {
            if ($folderFilter !== null && strcasecmp($folderName, $folderFilter) !== 0) {
                continue;
            }
            $folderPath = $platesRoot . DIRECTORY_SEPARATOR . $folderName;
            $setCode = $this->resolveSetCode($folderName, $folderPath);

            if (!$setCode) {
                $this->warn("Skipping {$folderName}: could not resolve set_code.");
                continue;
            }

            $plates = DB::table('plates')
                ->where('set_code', $setCode)
                ->whereNotNull('image_base')
                ->where('image_base', '!=', '')
                ->get(['image_base', 'image_ext', 'jurisdiction', 'variety_key', 'sort_order']);

            if ($plates->isEmpty()) {
                $this->warn("Skipping {$folderName}: no plates in DB for set_code {$setCode}.");
                continue;
            }

            $this->line("{$folderName} → set_code <info>{$setCode}</info> ({$plates->count()} catalog rows)");

            $folderRenamed = 0;
            $folderSkipped = 0;
            $folderErrors = 0;

            foreach ($this->imageFiles($folderPath) as $filePath) {
                $basename = basename($filePath);
                $parsed = $this->parseImageFilename($basename);

                if (!$parsed) {
                    $folderSkipped++;
                    continue;
                }

                if ($this->isAlreadyCatalogNamed($basename, $plates)) {
                    $folderSkipped++;
                    continue;
                }

                if ($this->isAlreadyRenamed($parsed['middle'], $parsed['side'], $plates)) {
                    $folderSkipped++;
                    continue;
                }

                $plate = $this->matchPlate($parsed['middle'], $plates, $parsed['variant_num'] ?? null);

                if (!$plate) {
                    $this->warn("  No match: {$basename}");
                    $folderErrors++;
                    continue;
                }

                $targetBase = $plate->image_base;
                $targetExt = strtolower($plate->image_ext ?: $parsed['ext']);
                $varietySuffix = $this->varietyFilenameSuffix($plate->variety_key ?? 'base');
                $targetName = $targetBase . $varietySuffix . '_' . $parsed['side'] . '.' . $targetExt;
                $targetPath = $folderPath . DIRECTORY_SEPARATOR . $targetName;

                if (strcasecmp($basename, $targetName) === 0) {
                    $folderSkipped++;
                    continue;
                }

                if (is_file($targetPath) && strcasecmp($basename, $targetName) !== 0) {
                    $this->error("  Collision: {$basename} → {$targetName} (target already exists)");
                    $folderErrors++;
                    continue;
                }

                $this->line("  {$basename} → {$targetName} ({$plate->jurisdiction})");

                if (!$dryRun) {
                    if (!rename($filePath, $targetPath)) {
                        $this->error("  Failed to rename {$basename}");
                        $folderErrors++;
                        continue;
                    }
                }

                $folderRenamed++;
            }

            $targetFolderPath = $platesRoot . DIRECTORY_SEPARATOR . $setCode;
            if (!$skipFolders && strcasecmp($folderName, $setCode) !== 0) {
                if (is_dir($targetFolderPath)) {
                    $this->error("  Cannot rename folder {$folderName} → {$setCode}: target folder already exists.");
                    $folderErrors++;
                } else {
                    $this->line("  Folder: {$folderName} → {$setCode}");
                    if (!$dryRun) {
                        if (!rename($folderPath, $targetFolderPath)) {
                            $this->error("  Failed to rename folder {$folderName}");
                            $folderErrors++;
                        }
                    }
                }
            }

            $totalRenamed += $folderRenamed;
            $totalSkipped += $folderSkipped;
            $totalErrors += $folderErrors;
        }

        $this->newLine();
        $this->info("Renamed: {$totalRenamed}, skipped: {$totalSkipped}, errors: {$totalErrors}");

        return $totalErrors > 0 ? 1 : 0;
    }

    private function resolveSetCode(string $folderName, string $folderPath): ?string
    {
        $explicit = [
            's63gm' => 'c63',
            's63po' => 'g63',
        ];

        if (isset($explicit[strtolower($folderName)])) {
            return $explicit[strtolower($folderName)];
        }

        if (preg_match('/^([a-z])(\d+)([a-z]+)$/i', $folderName, $matches)) {
            $suffix = strtolower($matches[3]);
            $digits = $matches[2];

            foreach (array_unique([$suffix . $digits, ($suffix[0] ?? '') . $digits]) as $candidate) {
                if (DB::table('plates')->where('set_code', $candidate)->exists()) {
                    return $candidate;
                }
            }
        }

        if (DB::table('plates')->where('set_code', $folderName)->exists()) {
            return $folderName;
        }

        $year = $this->inferYearFromFolder($folderPath);
        if (!$year) {
            return null;
        }

        $fileCount = count($this->imageFiles($folderPath));
        if ($fileCount === 0) {
            return null;
        }

        $bestCode = null;
        $bestDiff = PHP_INT_MAX;

        $candidates = DB::table('plates')
            ->where('year', $year)
            ->select('set_code', DB::raw('COUNT(*) as plate_count'))
            ->groupBy('set_code')
            ->get();

        foreach ($candidates as $candidate) {
            $diff = abs((int) $candidate->plate_count - $fileCount);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $bestCode = $candidate->set_code;
            }
        }

        return $bestDiff <= max(5, (int) floor($fileCount * 0.15)) ? $bestCode : null;
    }

    private function inferYearFromFolder(string $folderPath): ?int
    {
        $years = [];

        foreach ($this->imageFiles($folderPath) as $filePath) {
            if (preg_match('/^(\d{4})_/i', basename($filePath), $matches)) {
                $years[$matches[1]] = ($years[$matches[1]] ?? 0) + 1;
            }
        }

        if ($years === []) {
            return null;
        }

        arsort($years);

        return (int) array_key_first($years);
    }

    /**
     * @return list<string>
     */
    private function imageFiles(string $folderPath): array
    {
        $files = [];

        foreach (scandir($folderPath) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $folderPath . DIRECTORY_SEPARATOR . $entry;
            if (!is_file($path)) {
                continue;
            }

            if (preg_match('/_[ab]\.[^.]+$/i', $entry) || preg_match('/\d{2}[ab]\.[^.]+$/i', $entry)) {
                $files[] = $path;
            }
        }

        sort($files);

        return $files;
    }

    /**
     * @return array{middle: string, side: string, ext: string}|null
     */
    private function parseImageFilename(string $basename): ?array
    {
        if (preg_match('/^(\d{4})_(.+)_(\d{2})([ab])\.([^.]+)$/i', $basename, $matches)) {
            $middle = str_replace('_', '', strtolower($matches[2]));
            $middle = $this->filenameAliases[$middle] ?? $middle;

            return [
                'middle' => $middle,
                'side' => strtolower($matches[4]),
                'ext' => $matches[5],
                'variant_num' => (int) $matches[3],
            ];
        }

        if (!preg_match('/^(.+)_([ab])\.([^.]+)$/i', $basename, $matches)) {
            return null;
        }

        $middle = preg_replace('/^\d{4}_/', '', $matches[1]) ?? $matches[1];
        $middle = preg_replace('/^\d+_?/', '', $middle) ?? $middle;
        $middle = str_replace('_', '', strtolower($middle));
        $middle = preg_replace('/^z/', '', $middle) ?? $middle;
        $middle = $this->filenameAliases[$middle] ?? $middle;

        return [
            'middle' => $middle,
            'side' => strtolower($matches[2]),
            'ext' => $matches[3],
        ];
    }

    private function isAlreadyCatalogNamed(string $basename, $plates): bool
    {
        foreach ($plates as $plate) {
            $ext = strtolower($plate->image_ext ?: pathinfo($basename, PATHINFO_EXTENSION) ?: 'png');
            $expected = $plate->image_base
                . $this->varietyFilenameSuffix($plate->variety_key ?? 'base')
                . '_a.'
                . $ext;

            if (strcasecmp($basename, $expected) === 0) {
                return true;
            }
        }

        return false;
    }

    private function isAlreadyRenamed(string $middle, string $side, $plates): bool
    {
        foreach ($plates as $plate) {
            $stem = $this->normalize($plate->image_base) . $this->varietyFilenameSuffix($plate->variety_key ?? 'base');
            if (strcasecmp($stem, $this->normalize($middle)) === 0) {
                return true;
            }
        }

        return false;
    }

    private function varietyFilenameSuffix(?string $varietyKey): string
    {
        $varietyKey = trim((string) $varietyKey);

        if ($varietyKey === '' || $varietyKey === 'base') {
            return '';
        }

        return '_' . $varietyKey;
    }

    private function matchPlate(string $fileMiddle, $plates, ?int $variantNum = null): ?object
    {
        $fileSlug = $this->normalize($fileMiddle);
        $matches = [];

        foreach ($plates as $plate) {
            $jurisdictionSlug = $this->normalize($plate->jurisdiction ?? '');

            if ($jurisdictionSlug !== '' && $jurisdictionSlug === $fileSlug) {
                $matches[] = $plate;
            }
        }

        if ($matches === []) {
            return null;
        }

        usort($matches, fn ($a, $b) => ($a->sort_order ?? 0) <=> ($b->sort_order ?? 0));

        if ($variantNum !== null) {
            $typeNum = $variantNum;

            foreach ($matches as $plate) {
                if (preg_match('/Type\s*' . preg_quote((string) $typeNum, '/') . '\b/i', (string) ($plate->variety_notes ?? ''))) {
                    return $plate;
                }
            }

            $index = max(0, min($typeNum - 1, count($matches) - 1));

            return $matches[$index];
        }

        foreach ($matches as $plate) {
            if (trim((string) ($plate->variety_key ?? 'base')) === '' || ($plate->variety_key ?? 'base') === 'base') {
                return $plate;
            }
        }

        return $matches[0];
    }

    private function normalize(string $value): string
    {
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]/', '', $value) ?? $value;
        $value = str_replace('islands', 'is', $value);

        return $this->filenameAliases[$value] ?? $value;
    }
}

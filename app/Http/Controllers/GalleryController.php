<?php

namespace App\Http\Controllers;

use App\Support\WebPublicPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleryController extends Controller
{
    public function index()
    {
        $placeholder = asset('plate_missing.png');

        $sets = DB::table('plates')
            ->select(
                'set_code',
                DB::raw('MAX(set_name) as set_name'),
                DB::raw('MAX(company) as company'),
                DB::raw('MAX(cat_ref) as cat_ref'),
                DB::raw('MIN(year) as year')
            )
            ->groupBy('set_code')
            ->orderByRaw('MIN(year) IS NULL, MIN(year) ASC')
            ->orderBy('set_code')
            ->get()
            ->map(function ($set) use ($placeholder) {
                $set->sample_image_url = $this->randomSetFrontImageUrl($set->set_code) ?? $placeholder;

                return $set;
            });

        return view('gallery.index', [
            'sets' => $sets,
        ]);
    }

    public function show(Request $request, $setName)
    {
        $setName = urldecode($setName);

        $setCode = DB::table('plates')
            ->where('set_name', $setName)
            ->value('set_code');

        if (! $setCode) {
            return redirect()->route('gallery');
        }

        $setMeta = DB::table('plates')
            ->select(
                DB::raw('MAX(company) as company'),
                DB::raw('MIN(year) as year'),
                DB::raw('COUNT(*) as plate_count')
            )
            ->where('set_name', $setName)
            ->where('set_code', $setCode)
            ->first();

        $images = $this->collectSetImages($setCode);
        $sampleImage = $images[0]['a'] ?? asset('home_top_banner.jpg');

        $metaDescription = 'View miniature license plate images from the ' . $setName . ' set';
        if (! empty($setMeta?->company)) {
            $metaDescription .= ' by ' . $setMeta->company;
        }
        if (! empty($setMeta?->year)) {
            $metaDescription .= ' (' . $setMeta->year . ')';
        }
        if (! empty($setMeta?->plate_count)) {
            $metaDescription .= '. ' . number_format($setMeta->plate_count) . ' plates cataloged';
        }
        $metaDescription .= '. Browse front and back photos at MiniLicensePlates.com.';

        return view('gallery.show', [
            'selectedSet' => $setName,
            'folder' => $setCode,
            'images' => $images,
            'setMeta' => $setMeta,
            'sampleImage' => $sampleImage,
            'metaDescription' => $metaDescription,
        ]);
    }

    /**
     * @return list<array{a: string, b: string|null, jurisdiction: string|null, caption: string|null}>
     */
    private function collectSetImages(string $setCode): array
    {
        $dirPath = $this->resolveSetImageDirectory($setCode);
        $images = [];

        if ($dirPath === null) {
            return $images;
        }

        $plates = DB::table('plates')
            ->where('set_code', $setCode)
            ->whereNotNull('jurisdiction')
            ->orderBy('sort_order')
            ->get(['image_base', 'variety_key', 'variety_notes', 'jurisdiction', 'sort_order']);

        $jurisdictionByBase = $plates
            ->whereNotNull('image_base')
            ->pluck('jurisdiction', 'image_base');

        $jurisdictionFallbackByBase = DB::table('plates')
            ->whereNotNull('image_base')
            ->whereNotNull('jurisdiction')
            ->pluck('jurisdiction', 'image_base');

        $folderSegment = basename($dirPath);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

        foreach (scandir($dirPath) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (! in_array($ext, $allowedExtensions, true)) {
                continue;
            }

            $basename = pathinfo($file, PATHINFO_FILENAME);
            $parsed = $this->parseFrontImageBasename($basename);

            if ($parsed === null) {
                continue;
            }

            $aFile = asset('plates/' . $folderSegment . '/' . $file);
            $bFile = null;
            $plate = null;

            if ($parsed['type'] === 'standard') {
                $catalogBase = $this->resolveCatalogImageBase(
                    $parsed['stem'],
                    $jurisdictionByBase,
                    $jurisdictionFallbackByBase
                );
                $plate = $catalogBase !== null
                    ? $this->resolvePlateForStandardStem($parsed['stem'], $catalogBase, $plates)
                    : null;
                $bPath = $dirPath . '/' . $parsed['stem'] . '_b.' . $ext;
            } else {
                $plate = $this->resolvePlateForLegacyFile(
                    $setCode,
                    $parsed['jurisdiction_slug'],
                    $parsed['variant_num'] ?? null,
                    $plates
                );
                $backBasename = preg_replace('/(\d{2})a$/i', '$1b', $basename);
                $bPath = $dirPath . '/' . $backBasename . '.' . $ext;
            }

            if (file_exists($bPath)) {
                $bFile = asset('plates/' . $folderSegment . '/' . basename($bPath));
            }

            $jurisdiction = $plate->jurisdiction ?? null;

            $images[] = [
                'a' => $aFile,
                'b' => $bFile,
                'jurisdiction' => $jurisdiction,
                'caption' => $this->formatGalleryCaption($plate),
                'sort_order' => $plate->sort_order ?? PHP_INT_MAX,
            ];
        }

        usort($images, function (array $left, array $right): int {
            if ($left['sort_order'] !== $right['sort_order']) {
                return $left['sort_order'] <=> $right['sort_order'];
            }

            return strcmp($left['a'], $right['a']);
        });

        foreach ($images as &$image) {
            unset($image['sort_order']);
        }
        unset($image);

        return $images;
    }

    private function resolvePlateForStandardStem(string $fileStem, string $catalogBase, $plates): ?object
    {
        $varietyKey = $this->varietyKeyFromStem($fileStem, $catalogBase);

        foreach ($plates as $plate) {
            if (($plate->image_base ?? '') !== $catalogBase) {
                continue;
            }

            if ($this->normalizedVarietyKey($plate->variety_key ?? 'base') === $varietyKey) {
                return $plate;
            }
        }

        return $plates->first(fn ($plate) => ($plate->image_base ?? '') === $catalogBase);
    }

    private function resolvePlateForLegacyFile(string $setCode, string $slug, ?int $variantNum, $plates): ?object
    {
        $jurisdiction = $this->resolveJurisdictionFromSlug($setCode, $slug);

        if ($jurisdiction === null) {
            return null;
        }

        $matches = $plates
            ->filter(fn ($plate) => ($plate->jurisdiction ?? '') === $jurisdiction)
            ->values();

        if ($matches->isEmpty()) {
            return null;
        }

        if ($variantNum !== null) {
            $typeNum = $variantNum;

            foreach ($matches as $plate) {
                if (preg_match('/Type\s*' . preg_quote((string) $typeNum, '/') . '\b/i', (string) ($plate->variety_notes ?? ''))) {
                    return $plate;
                }
            }

            $index = max(0, min($typeNum - 1, $matches->count() - 1));

            return $matches[$index];
        }

        return $matches->first();
    }

    private function varietyKeyFromStem(string $fileStem, string $catalogBase): string
    {
        if ($fileStem === $catalogBase) {
            return 'base';
        }

        if (str_starts_with($fileStem, $catalogBase . '_')) {
            return substr($fileStem, strlen($catalogBase) + 1);
        }

        return 'base';
    }

    private function normalizedVarietyKey(?string $varietyKey): string
    {
        $varietyKey = trim((string) $varietyKey);

        return $varietyKey === '' ? 'base' : $varietyKey;
    }

    private function formatGalleryCaption(?object $plate): ?string
    {
        if ($plate === null || empty($plate->jurisdiction)) {
            return null;
        }

        $variant = trim((string) ($plate->variety_notes ?? ''));

        if ($variant === '') {
            $varietyKey = $this->normalizedVarietyKey($plate->variety_key ?? 'base');
            if ($varietyKey !== 'base') {
                $variant = $varietyKey;
            }
        }

        if ($variant === '') {
            return $plate->jurisdiction;
        }

        return $plate->jurisdiction . ' (' . $variant . ')';
    }

    /**
     * @return array{type: string, stem?: string, jurisdiction_slug?: string}|null
     */
    private function parseFrontImageBasename(string $basename): ?array
    {
        if (preg_match('/_a$/i', $basename)) {
            return [
                'type' => 'standard',
                'stem' => preg_replace('/_a$/i', '', $basename),
            ];
        }

        if (preg_match('/^\d{4}_(.+)_(\d{2})a$/i', $basename, $matches)) {
            return [
                'type' => 'legacy_numbered',
                'jurisdiction_slug' => $matches[1],
                'variant_num' => (int) $matches[2],
            ];
        }

        return null;
    }

    private function resolveJurisdictionFromSlug(string $setCode, string $slug): ?string
    {
        $fileSlug = $this->normalizeJurisdictionSlug($slug);
        $matches = [];

        foreach ($this->jurisdictionsForSet($setCode) as $jurisdiction) {
            $jurisdictionSlug = $this->normalizeJurisdictionSlug($jurisdiction);

            if ($jurisdictionSlug === ''
                || ($fileSlug !== $jurisdictionSlug
                    && ! str_contains($fileSlug, $jurisdictionSlug)
                    && ! str_contains($jurisdictionSlug, $fileSlug))) {
                continue;
            }

            $matches[] = $jurisdiction;
        }

        if ($matches === []) {
            return null;
        }

        $exact = array_values(array_filter(
            $matches,
            fn (string $jurisdiction) => $this->normalizeJurisdictionSlug($jurisdiction) === $fileSlug
        ));

        if (count($exact) === 1) {
            return $exact[0];
        }

        usort($matches, fn (string $a, string $b) => strlen($b) <=> strlen($a));

        return $matches[0];
    }

    /**
     * @return list<string>
     */
    private function jurisdictionsForSet(string $setCode): array
    {
        static $cache = [];

        if (! isset($cache[$setCode])) {
            $cache[$setCode] = DB::table('plates')
                ->where('set_code', $setCode)
                ->whereNotNull('jurisdiction')
                ->distinct()
                ->orderBy('jurisdiction')
                ->pluck('jurisdiction')
                ->all();
        }

        return $cache[$setCode];
    }

    private function normalizeJurisdictionSlug(string $value): string
    {
        $value = strtolower($value);
        $value = str_replace('_', '', $value);
        $value = preg_replace('/[^a-z0-9]/', '', $value) ?? $value;
        $value = str_replace('islands', 'is', $value);

        $aliases = [
            'connectucut' => 'connecticut',
            'vitrginia' => 'virginia',
            'mass' => 'massachusetts',
            'guatemala' => 'guatamala',
            'britishguiana' => 'britishguyana',
            'pei' => 'princeedwardis',
            'districtcolumbia' => 'distofcolumbia',
            'districtofcolumbia' => 'distofcolumbia',
            'princeedwardisland' => 'princeedwardis',
        ];

        return $aliases[$value] ?? $value;
    }

    private function resolveSetImageDirectory(string $setCode): ?string
    {
        $candidates = [$setCode];

        if ($setCode === 'm53gm') {
            $candidates[] = 'g53';
            $candidates[] = 'gm53';
        }

        foreach ($candidates as $candidate) {
            $path = WebPublicPath::path('plates/' . $candidate);
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Map a front-image file stem (e.g. AL, AL_a) to catalog image_base (e.g. AL).
     *
     * @param  \Illuminate\Support\Collection<string, string>  $jurisdictionByBase
     * @param  \Illuminate\Support\Collection<string, string>  $jurisdictionFallbackByBase
     */
    private function resolveCatalogImageBase(
        string $fileStem,
        $jurisdictionByBase,
        $jurisdictionFallbackByBase
    ): ?string {
        if ($jurisdictionByBase->has($fileStem) || $jurisdictionFallbackByBase->has($fileStem)) {
            return $fileStem;
        }

        $knownBases = $jurisdictionByBase->keys()
            ->merge($jurisdictionFallbackByBase->keys())
            ->unique()
            ->sortByDesc(fn (string $base) => strlen($base))
            ->values();

        foreach ($knownBases as $base) {
            if ($fileStem === $base || str_starts_with($fileStem, $base . '_')) {
                return $base;
            }
        }

        return null;
    }

    private function randomSetFrontImageUrl(string $setCode): ?string
    {
        $images = $this->collectSetImages($setCode);

        if ($images === []) {
            return null;
        }

        return $images[array_rand($images)]['a'];
    }
}

<?php

namespace App\Http\Controllers;

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
     * @return list<array{a: string, b: string|null}>
     */
    private function collectSetImages(string $setCode): array
    {
        $dirPath = public_path('plates/' . $setCode);
        $images = [];

        if (! is_dir($dirPath)) {
            return $images;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

        foreach (scandir($dirPath) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $basename = pathinfo($file, PATHINFO_FILENAME);

            if (preg_match('/_a$/i', $basename) && in_array($ext, $allowedExtensions, true)) {
                $baseNoSuffix = preg_replace('/_a$/i', '', $basename);
                $aFile = asset('plates/' . $setCode . '/' . $file);
                $bPath = $dirPath . '/' . $baseNoSuffix . '_b.' . $ext;
                $bFile = file_exists($bPath)
                    ? asset('plates/' . $setCode . '/' . $baseNoSuffix . '_b.' . $ext)
                    : null;

                $images[] = ['a' => $aFile, 'b' => $bFile];
            }
        }

        return $images;
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

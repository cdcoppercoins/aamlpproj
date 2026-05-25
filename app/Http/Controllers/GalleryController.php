<?php

namespace App\Http\Controllers;

use App\Models\Plate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleryController extends Controller
{
    private const SET_TYPE_OPTIONS = [
        'm' => 'metal plates',
        'c' => 'plate cards',
        's' => 'plate stickers',
        'x' => 'other',
    ];

    private const SET_TYPE_CODES = ['m', 'c', 's', 'x'];

    public function index(Request $request)
    {
        $filterOptions = [
            'years' => DB::table('plates')
                ->whereNotNull('year')
                ->distinct()
                ->orderBy('year')
                ->pluck('year'),
            'jurisdictions' => DB::table('plates')
                ->whereNotNull('jurisdiction')
                ->where('jurisdiction', '!=', '')
                ->distinct()
                ->orderBy('jurisdiction')
                ->pluck('jurisdiction'),
            'setNames' => DB::table('plates')
                ->distinct()
                ->orderBy('set_name')
                ->pluck('set_name'),
            'companies' => DB::table('plates')
                ->whereNotNull('company')
                ->where('company', '!=', '')
                ->distinct()
                ->orderBy('company')
                ->pluck('company'),
        ];

        $totalCount = DB::table('plates')->count();
        $hasSearch = $request->boolean('search');
        $results = null;

        if ($hasSearch) {
            $query = Plate::query();

            if ($request->filled('year')) {
                $query->where('year', $request->integer('year'));
            }

            if ($request->filled('jurisdiction')) {
                $query->where('jurisdiction', $request->jurisdiction);
            }

            if ($request->filled('set_name')) {
                $query->where('set_name', $request->set_name);
            }

            if ($request->filled('company')) {
                $query->where('company', $request->company);
            }

            $setTypes = $this->normalizeSetTypes($request->input('set_types', []));
            if ($setTypes !== []) {
                $query->where(function ($typeQuery) use ($setTypes) {
                    foreach ($setTypes as $type) {
                        if ($type === 'x') {
                            $typeQuery->orWhereRaw('LOWER(LEFT(set_code, 1)) NOT IN (?, ?, ?)', ['m', 'c', 's']);
                        } else {
                            $typeQuery->orWhereRaw('LOWER(LEFT(set_code, 1)) = ?', [$type]);
                        }
                    }
                });
            }

            $results = $query
                ->orderBy('set_name')
                ->orderBy('sort_order')
                ->orderBy('jurisdiction')
                ->paginate(12)
                ->appends($request->query());
        }

        $setCounts = collect();
        if ($results && $results->count() > 0) {
            $setCounts = DB::table('plates')
                ->select('set_code', DB::raw('COUNT(*) as total'))
                ->whereIn('set_code', $results->pluck('set_code')->unique())
                ->groupBy('set_code')
                ->pluck('total', 'set_code');
        }

        return view('gallery', [
            'filterOptions' => $filterOptions,
            'setTypeOptions' => self::SET_TYPE_OPTIONS,
            'totalCount' => $totalCount,
            'hasSearch' => $hasSearch,
            'results' => $results,
            'setCounts' => $setCounts,
            'filters' => array_merge(
                $request->only(['year', 'jurisdiction', 'set_name', 'company']),
                ['set_types' => $this->normalizeSetTypes($request->input('set_types', []))]
            ),
        ]);
    }

    /**
     * @param  mixed  $setTypes
     * @return list<string>
     */
    private function normalizeSetTypes($setTypes): array
    {
        if (! is_array($setTypes)) {
            return [];
        }

        return array_values(array_intersect($setTypes, self::SET_TYPE_CODES));
    }

    public function show(Request $request, $setName)
    {
        $setName = urldecode($setName);

        $setCode = DB::table('plates')
            ->where('set_name', $setName)
            ->value('set_code');

        if (!$setCode) {
            return redirect()->route('gallery');
        }

        $dirPath = public_path('plates/' . $setCode);
        $images = [];

        if (is_dir($dirPath)) {
            $files = scandir($dirPath);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $basename = pathinfo($file, PATHINFO_FILENAME);

                if (preg_match('/_a$/i', $basename) && in_array($ext, $allowedExtensions)) {
                    $baseNoSuffix = preg_replace('/_a$/i', '', $basename);
                    $aFile = asset('plates/' . $setCode . '/' . $file);
                    $bPath = $dirPath . '/' . $baseNoSuffix . '_b.' . $ext;
                    $bFile = file_exists($bPath)
                        ? asset('plates/' . $setCode . '/' . $baseNoSuffix . '_b.' . $ext)
                        : null;

                    $images[] = ['a' => $aFile, 'b' => $bFile];
                }
            }
        }

        $infoPath = resource_path('views/setinfo/' . $setCode . '_info.blade.php');
        $varPath = resource_path('views/setinfo/' . $setCode . '_varieties.blade.php');

        return view('gallery.show', [
            'selectedSet' => $setName,
            'folder' => $setCode,
            'images' => $images,
            'hasInfo' => file_exists($infoPath),
            'hasVarieties' => file_exists($varPath),
        ]);
    }
}

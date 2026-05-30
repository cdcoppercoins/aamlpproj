<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plate;
use App\Support\WebPublicPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogSetController extends Controller
{
    public function index(Request $request): View
    {
        $query = DB::table('plates')
            ->select(
                'set_code',
                DB::raw('MAX(set_name) as set_name'),
                DB::raw('MAX(company) as company'),
                DB::raw('MIN(year) as year'),
                DB::raw('COUNT(*) as plate_count')
            )
            ->groupBy('set_code')
            ->orderBy('set_name');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('set_code', 'like', "%{$search}%")
                    ->orWhere('set_name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $sets = $query->paginate(30)->withQueryString();

        return view('admin.catalog.sets.index', compact('sets', 'search'));
    }

    public function create(): View
    {
        return view('admin.catalog.sets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'set_code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('plates', 'set_code'),
            ],
            'set_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:128'],
            'year' => ['nullable', 'integer', 'min:1800', 'max:2100'],
        ], [], [
            'set_code' => 'set code',
            'set_name' => 'set name',
        ]);

        $directory = WebPublicPath::path('plates/' . $validated['set_code']);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        session([
            'admin_pending_sets.' . $validated['set_code'] => [
                'set_name' => $validated['set_name'],
                'company' => $validated['company'] ?? null,
                'year' => $validated['year'] ?? null,
            ],
        ]);

        return redirect()
            ->route('admin.catalog.plates.create', $validated['set_code'])
            ->with('success', 'Set folder ready. Add the first catalog plate below.');
    }

    public function show(Request $request, string $setCode): View|RedirectResponse
    {
        $setMeta = $this->resolveSetMeta($setCode);

        if ($setMeta === null) {
            return redirect()
                ->route('admin.catalog.sets.index')
                ->with('error', 'Set not found.');
        }

        $platesQuery = Plate::query()
            ->where('set_code', $setCode)
            ->orderBy('sort_order')
            ->orderBy('jurisdiction')
            ->orderBy('serial_number')
            ->orderBy('id');

        if ($search = trim((string) $request->query('q'))) {
            $platesQuery->where(function ($builder) use ($search) {
                $builder->where('jurisdiction', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('cat_ref', 'like', "%{$search}%")
                    ->orWhere('variety_key', 'like', "%{$search}%");
            });
        }

        $plates = $platesQuery->paginate(50)->withQueryString();

        return view('admin.catalog.sets.show', compact('setMeta', 'plates', 'search'));
    }

    public function edit(string $setCode): View|RedirectResponse
    {
        $setMeta = $this->resolveSetMeta($setCode);

        if ($setMeta === null) {
            return redirect()
                ->route('admin.catalog.sets.index')
                ->with('error', 'Set not found.');
        }

        return view('admin.catalog.sets.edit', compact('setMeta'));
    }

    public function update(Request $request, string $setCode): RedirectResponse
    {
        if ($this->resolveSetMeta($setCode) === null) {
            return redirect()
                ->route('admin.catalog.sets.index')
                ->with('error', 'Set not found.');
        }

        $validated = $request->validate([
            'set_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:128'],
            'year' => ['nullable', 'integer', 'min:1800', 'max:2100'],
        ]);

        Plate::query()
            ->where('set_code', $setCode)
            ->update([
                'set_name' => $validated['set_name'],
                'company' => $validated['company'] ?? null,
                'year' => $validated['year'] ?? null,
                'updated_at' => now(),
            ]);

        session()->forget('admin_pending_sets.' . $setCode);

        return redirect()
            ->route('admin.catalog.sets.show', $setCode)
            ->with('success', 'Set details updated for all plates in this set.');
    }

    public function destroy(string $setCode): RedirectResponse
    {
        if ($this->resolveSetMeta($setCode) === null) {
            return redirect()
                ->route('admin.catalog.sets.index')
                ->with('error', 'Set not found.');
        }

        Plate::query()->where('set_code', $setCode)->delete();

        $directory = WebPublicPath::path('plates/' . $setCode);
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }

        session()->forget('admin_pending_sets.' . $setCode);

        return redirect()
            ->route('admin.catalog.sets.index')
            ->with('success', "Deleted set \"{$setCode}\" and all catalog plates in it.");
    }

    private function resolveSetMeta(string $setCode): ?object
    {
        $meta = DB::table('plates')
            ->select(
                'set_code',
                DB::raw('MAX(set_name) as set_name'),
                DB::raw('MAX(company) as company'),
                DB::raw('MIN(year) as year'),
                DB::raw('COUNT(*) as plate_count')
            )
            ->where('set_code', $setCode)
            ->groupBy('set_code')
            ->first();

        if ($meta) {
            return $meta;
        }

        $pending = session('admin_pending_sets.' . $setCode);

        if (! is_array($pending)) {
            return null;
        }

        return (object) [
            'set_code' => $setCode,
            'set_name' => $pending['set_name'],
            'company' => $pending['company'] ?? null,
            'year' => $pending['year'] ?? null,
            'plate_count' => 0,
        ];
    }
}

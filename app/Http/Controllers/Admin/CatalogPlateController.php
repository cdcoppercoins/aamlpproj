<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plate;
use App\Services\PlateImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogPlateController extends Controller
{
    public const JURISDICTION_TYPES = [
        'us_state' => 'US state',
        'ca_province' => 'Canadian province',
        'ca_territory' => 'Canadian territory',
        'foreign_country' => 'Foreign country',
    ];

    public function __construct(
        private readonly PlateImageStorage $imageStorage,
    ) {}

    public function create(string $setCode): View|RedirectResponse
    {
        $setMeta = $this->resolveSetMeta($setCode);

        if ($setMeta === null) {
            return redirect()
                ->route('admin.catalog.sets.index')
                ->with('error', 'Set not found. Create a set first.');
        }

        return view('admin.catalog.plates.create', [
            'setMeta' => $setMeta,
            'plate' => new Plate([
                'set_code' => $setCode,
                'set_name' => $setMeta->set_name,
                'company' => $setMeta->company,
                'year' => $setMeta->year,
                'variety_key' => 'base',
                'sort_order' => 0,
            ]),
            'jurisdictionTypes' => self::JURISDICTION_TYPES,
        ]);
    }

    public function store(Request $request, string $setCode): RedirectResponse
    {
        $setMeta = $this->resolveSetMeta($setCode);

        if ($setMeta === null) {
            return redirect()
                ->route('admin.catalog.sets.index')
                ->with('error', 'Set not found.');
        }

        $validated = $this->normalizePlateInput($this->validatePlate($request, $setCode));

        $validated['set_code'] = $setCode;
        $validated['set_name'] = $setMeta->set_name;
        $validated['company'] = $setMeta->company;
        $validated['year'] = $setMeta->year;
        $validated['variety_key'] = $validated['variety_key'] ?: 'base';

        if (empty($validated['image_base'])) {
            $validated['image_base'] = 'nophoto-' . uniqid();
        }

        $plate = Plate::create($validated);

        if ($request->hasFile('front_image')) {
            $imageData = $this->imageStorage->storeFrontImage(
                $plate,
                $request->file('front_image'),
                $request->input('image_base')
            );
            $plate->fill($imageData)->save();
        }

        if ($request->hasFile('back_image')) {
            $this->imageStorage->storeBackImage($plate, $request->file('back_image'));
        }

        session()->forget('admin_pending_sets.' . $setCode);

        return redirect()
            ->route('admin.catalog.sets.show', $setCode)
            ->with('success', 'Catalog plate added.');
    }

    public function edit(Plate $plate): View
    {
        return view('admin.catalog.plates.edit', [
            'plate' => $plate,
            'setMeta' => $this->resolveSetMeta($plate->set_code),
            'jurisdictionTypes' => self::JURISDICTION_TYPES,
        ]);
    }

    public function update(Request $request, Plate $plate): RedirectResponse
    {
        $validated = $this->normalizePlateInput($this->validatePlate($request, $plate->set_code, $plate));
        $validated['variety_key'] = $validated['variety_key'] ?: 'base';

        $plate->fill($validated);

        if ($request->boolean('remove_front_image')) {
            $this->imageStorage->deleteImages($plate, front: true, back: false);
            $plate->image_base = null;
            $plate->image_ext = null;
            $plate->has_back_image = null;
        }

        if ($request->boolean('remove_back_image')) {
            $this->imageStorage->deleteImages($plate, front: false, back: true);
            $plate->has_back_image = 0;
        }

        $plate->save();

        if ($request->hasFile('front_image')) {
            $this->imageStorage->deleteImages($plate, front: true, back: false);
            $imageData = $this->imageStorage->storeFrontImage(
                $plate,
                $request->file('front_image'),
                $request->input('image_base')
            );
            $plate->fill($imageData)->save();
        }

        if ($request->hasFile('back_image')) {
            $this->imageStorage->deleteImages($plate, front: false, back: true);
            $this->imageStorage->storeBackImage($plate, $request->file('back_image'));
        }

        return redirect()
            ->route('admin.catalog.plates.edit', $plate)
            ->with('success', 'Catalog plate updated.');
    }

    public function destroy(Plate $plate): RedirectResponse
    {
        $setCode = $plate->set_code;

        $this->imageStorage->deleteImages($plate);
        $plate->delete();

        return redirect()
            ->route('admin.catalog.sets.show', $setCode)
            ->with('success', 'Catalog plate deleted.');
    }

    private function validatePlate(Request $request, string $setCode, ?Plate $plate = null): array
    {
        $varietyKey = $request->input('variety_key') ?: 'base';

        $imageBaseRules = ['nullable', 'string', 'max:128'];

        if ($request->filled('image_base')) {
            $uniqueRule = Rule::unique('plates')
                ->where(fn ($query) => $query
                    ->where('set_code', $setCode)
                    ->where('variety_key', $varietyKey));

            if ($plate) {
                $uniqueRule->ignore($plate->id);
            }

            $imageBaseRules[] = $uniqueRule;
        }

        return $request->validate([
            'cat_ref' => ['nullable', 'string', 'max:10'],
            'image_base' => $imageBaseRules,
            'image_ext' => ['nullable', 'string', 'max:16'],
            'has_back_image' => ['nullable', 'in:0,1,'],
            'jurisdiction' => ['nullable', 'string', 'max:128'],
            'jurisdiction_type' => ['nullable', 'string', Rule::in(array_keys(self::JURISDICTION_TYPES))],
            'year' => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'serial_number' => ['nullable', 'string', 'max:64'],
            'width_inches' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'height_inches' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'value_mt' => ['nullable', 'string', 'max:32'],
            'value_ex' => ['nullable', 'string', 'max:32'],
            'value_vg' => ['nullable', 'string', 'max:32'],
            'value_g' => ['nullable', 'string', 'max:32'],
            'value_fr' => ['nullable', 'string', 'max:32'],
            'value_po' => ['nullable', 'string', 'max:32'],
            'variety_key' => ['nullable', 'string', 'max:32'],
            'variety_notes' => ['nullable', 'string'],
            'state_embossed' => ['nullable', 'in:0,1,'],
            'legend_embossed' => ['nullable', 'in:0,1,'],
            'notes' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'front_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'back_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'remove_front_image' => ['nullable', 'boolean'],
            'remove_back_image' => ['nullable', 'boolean'],
        ], [], [
            'cat_ref' => 'catalog reference',
            'image_base' => 'image base name',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizePlateInput(array $validated): array
    {
        foreach (['has_back_image', 'state_embossed', 'legend_embossed'] as $field) {
            if (! array_key_exists($field, $validated) || $validated[$field] === '') {
                $validated[$field] = null;
            } else {
                $validated[$field] = (int) $validated[$field];
            }
        }

        foreach (['image_base', 'image_ext', 'jurisdiction', 'jurisdiction_type', 'serial_number', 'variety_key', 'variety_notes', 'notes', 'cat_ref'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        foreach (['value_mt', 'value_ex', 'value_vg', 'value_g', 'value_fr', 'value_po'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        return $validated;
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

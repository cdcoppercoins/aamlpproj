<?php

namespace App\Http\Controllers;

use App\Models\CollectionItem;
use App\Models\CollectionOwnedItem;
use App\Models\CollectionSetSetting;
use App\Models\Plate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function guide()
    {
        return view('collection.guide');
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $setSummaries = DB::table('collection_items')
            ->join('plates', 'plates.id', '=', 'collection_items.plate_id')
            ->leftJoin('collection_owned_items', 'collection_owned_items.collection_item_id', '=', 'collection_items.id')
            ->leftJoin('collection_set_settings', function ($join) use ($userId) {
                $join->on('collection_set_settings.set_code', '=', 'plates.set_code')
                    ->where('collection_set_settings.user_id', '=', $userId);
            })
            ->where('collection_items.user_id', $userId)
            ->groupBy('plates.set_code')
            ->select(
                'plates.set_code',
                DB::raw('MAX(plates.set_name) as set_name'),
                DB::raw('MAX(plates.company) as company'),
                DB::raw('MIN(plates.year) as year'),
                DB::raw('COUNT(DISTINCT collection_items.id) as entry_count'),
                DB::raw('COUNT(CASE WHEN collection_items.is_wanted = 0 THEN collection_owned_items.id END) as owned_qty'),
                DB::raw('SUM(CASE WHEN collection_items.is_wanted = 1 THEN 1 ELSE 0 END) as wanted_count'),
                DB::raw('COALESCE(MAX(collection_set_settings.is_public), 0) as is_public')
            )
            ->orderBy('set_name')
            ->get();

        $stats = [
            'owned' => DB::table('collection_owned_items')
                ->join('collection_items', 'collection_items.id', '=', 'collection_owned_items.collection_item_id')
                ->where('collection_items.user_id', $userId)
                ->where('collection_items.is_wanted', false)
                ->count(),
            'wanted' => Auth::user()->collectionItems()->where('is_wanted', true)->count(),
            'distinct_owned' => Auth::user()->collectionItems()->where('is_wanted', false)->whereHas('ownedItems')->count(),
            'set_count' => $setSummaries->count(),
        ];

        $publicCollectors = $this->publicCollectorsQuery($userId)->get();

        $itemsBySetCode = Auth::user()
            ->collectionItems()
            ->with(['plate', 'ownedItems'])
            ->whereHas('plate')
            ->get()
            ->groupBy(fn (CollectionItem $item) => $item->plate->set_code);

        $catalogTotal = CollectionItem::sumOwnedLineValues(
            Auth::user()->collectionItems()->with(['plate', 'ownedItems'])->where('is_wanted', false)->get()
        );

        foreach ($setSummaries as $set) {
            $set->catalog_total = CollectionItem::sumOwnedLineValues(
                $itemsBySetCode->get($set->set_code, collect())
            );
        }

        $stats['catalog_total'] = $catalogTotal;

        return view('collection.index', [
            'setSummaries' => $setSummaries,
            'stats' => $stats,
            'publicCollectors' => $publicCollectors,
        ]);
    }

    public function updateSetVisibility(Request $request, string $setCode)
    {
        $validated = $request->validate([
            'is_public' => ['required', 'boolean'],
        ]);

        $isPublic = $request->boolean('is_public');

        $hasItems = CollectionItem::query()
            ->where('user_id', Auth::id())
            ->whereHas('plate', fn ($q) => $q->where('set_code', $setCode))
            ->exists();

        if (! $hasItems) {
            return back()->with('error', 'You have no entries in that set.');
        }

        CollectionSetSetting::updateOrCreate(
            ['user_id' => Auth::id(), 'set_code' => $setCode],
            ['is_public' => $isPublic]
        );

        $label = $isPublic ? 'public' : 'private';

        return back()->with('success', "Set visibility updated to {$label}.");
    }

    public function showMember(Request $request, string $username)
    {
        $member = User::query()->where('username', $username)->firstOrFail();

        if ($member->id === Auth::id()) {
            return redirect()->route('collection.index');
        }

        $setName = $request->query('set_name');

        if ($setName) {
            return $this->showMemberSet($member, $setName);
        }

        $publicSets = $this->memberPublicSetSummaries($member->id)->get();

        if ($publicSets->isEmpty()) {
            abort(404);
        }

        $totalOwned = $publicSets->sum('owned_qty');

        return view('collection.member', [
            'member' => $member,
            'publicSets' => $publicSets,
            'totalOwned' => $totalOwned,
        ]);
    }

    private function showMemberSet(User $member, string $setName)
    {
        $setMeta = DB::table('plates')
            ->select(
                'set_code',
                DB::raw('MAX(set_name) as set_name'),
                DB::raw('MAX(company) as company'),
                DB::raw('MIN(year) as year')
            )
            ->where('set_name', $setName)
            ->groupBy('set_code')
            ->first();

        if (! $setMeta) {
            abort(404);
        }

        if (! $this->setIsPublicForUser($member->id, $setMeta->set_code)) {
            abort(403);
        }

        $plates = Plate::query()
            ->where('set_name', $setName)
            ->orderedForCatalog()
            ->get();

        $collectionByPlateId = CollectionItem::query()
            ->with('ownedItems')
            ->where('user_id', $member->id)
            ->whereIn('plate_id', $plates->pluck('id'))
            ->get()
            ->keyBy('plate_id');

        $entries = $plates->filter(fn (Plate $plate) => $collectionByPlateId->has($plate->id))->values();

        if ($entries->isEmpty()) {
            abort(404);
        }

        return view('collection.member-set', [
            'member' => $member,
            'setMeta' => $setMeta,
            'entries' => $entries,
            'collectionByPlateId' => $collectionByPlateId,
            'usesCatalogCardNumbers' => Plate::platesUseCatalogCardNumbers($plates),
        ]);
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function publicCollectorsQuery(int $excludeUserId)
    {
        return DB::table('users')
            ->join('collection_set_settings', 'users.id', '=', 'collection_set_settings.user_id')
            ->join('collection_items', function ($join) {
                $join->on('collection_items.user_id', '=', 'users.id');
            })
            ->join('plates', function ($join) {
                $join->on('plates.id', '=', 'collection_items.plate_id')
                    ->on('plates.set_code', '=', 'collection_set_settings.set_code');
            })
            ->leftJoin('collection_owned_items', 'collection_owned_items.collection_item_id', '=', 'collection_items.id')
            ->where('collection_set_settings.is_public', true)
            ->where('users.id', '!=', $excludeUserId)
            ->groupBy('users.id', 'users.username', 'users.name', 'users.profile_image')
            ->select(
                'users.id',
                'users.username',
                'users.name',
                'users.profile_image',
                DB::raw('COUNT(CASE WHEN collection_items.is_wanted = 0 THEN collection_owned_items.id END) as owned_qty'),
                DB::raw('COUNT(DISTINCT plates.set_code) as public_set_count')
            )
            ->having('owned_qty', '>', 0)
            ->orderBy('users.username');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function memberPublicSetSummaries(int $userId)
    {
        return DB::table('collection_set_settings')
            ->join('collection_items', 'collection_items.user_id', '=', 'collection_set_settings.user_id')
            ->join('plates', function ($join) {
                $join->on('plates.id', '=', 'collection_items.plate_id')
                    ->on('plates.set_code', '=', 'collection_set_settings.set_code');
            })
            ->leftJoin('collection_owned_items', 'collection_owned_items.collection_item_id', '=', 'collection_items.id')
            ->where('collection_set_settings.user_id', $userId)
            ->where('collection_set_settings.is_public', true)
            ->groupBy('plates.set_code')
            ->select(
                'plates.set_code',
                DB::raw('MAX(plates.set_name) as set_name'),
                DB::raw('MAX(plates.company) as company'),
                DB::raw('MIN(plates.year) as year'),
                DB::raw('COUNT(DISTINCT collection_items.id) as entry_count'),
                DB::raw('COUNT(CASE WHEN collection_items.is_wanted = 0 THEN collection_owned_items.id END) as owned_qty'),
                DB::raw('SUM(CASE WHEN collection_items.is_wanted = 1 THEN 1 ELSE 0 END) as wanted_count')
            )
            ->orderBy('set_name');
    }

    private function setIsPublicForUser(int $userId, string $setCode): bool
    {
        return CollectionSetSetting::query()
            ->where('user_id', $userId)
            ->where('set_code', $setCode)
            ->where('is_public', true)
            ->exists();
    }

    /**
     * @param  Collection<int, Plate>  $plates
     * @param  Collection<int, CollectionItem>  $collectionByPlateId
     */
    private function catalogTotalForSet(Collection $plates, Collection $collectionByPlateId): ?float
    {
        $items = $plates
            ->map(fn (Plate $plate) => $collectionByPlateId->get($plate->id))
            ->filter()
            ->each(function (CollectionItem $item) use ($plates) {
                $plate = $plates->firstWhere('id', $item->plate_id);
                if ($plate) {
                    $item->setRelation('plate', $plate);
                }
                if (! $item->relationLoaded('ownedItems')) {
                    $item->load('ownedItems');
                }
            });

        return CollectionItem::sumOwnedLineValues($items);
    }

    public function manage(Request $request)
    {
        $setNames = $this->catalogSetNames();

        $setName = $request->query('set_name');

        if ($setName === null || $setName === '') {
            return view('collection.manage', [
                'setNames' => $setNames,
                'selectedSet' => null,
                'setMeta' => null,
                'plates' => null,
                'collectionByPlateId' => collect(),
                'grades' => CollectionItem::GRADES,
            ]);
        }

        $setData = $this->resolveSetCollectionData($setName);

        if ($setData === null) {
            return redirect()
                ->route('collection.manage')
                ->with('error', 'Set not found. Choose a set from the list.');
        }

        $setCatalogTotal = $this->catalogTotalForSet(
            $setData['plates'],
            $setData['collectionByPlateId']
        );

        return view('collection.manage', [
            'setNames' => $setNames,
            'selectedSet' => $setName,
            'setMeta' => $setData['setMeta'],
            'plates' => $setData['plates'],
            'collectionByPlateId' => $setData['collectionByPlateId'],
            'grades' => CollectionItem::GRADES,
            'setCatalogTotal' => $setCatalogTotal,
            'usesCatalogCardNumbers' => $setData['usesCatalogCardNumbers'],
        ]);
    }

    public function managePdf(Request $request)
    {
        $validated = $request->validate([
            'set_name' => ['required', 'string', 'max:255'],
            'scope' => ['nullable', Rule::in(['checklist', 'mine'])],
        ]);

        $setData = $this->resolveSetCollectionData($validated['set_name']);

        if ($setData === null) {
            return redirect()
                ->route('collection.manage')
                ->with('error', 'Set not found.');
        }

        $scope = $validated['scope'] ?? 'checklist';
        $plates = $setData['plates'];
        $collectionByPlateId = $setData['collectionByPlateId'];

        if ($scope === 'mine') {
            $plates = $plates->filter(
                fn (Plate $plate) => $collectionByPlateId->has($plate->id)
            )->values();
        }

        if ($plates->isEmpty()) {
            return redirect()
                ->route('collection.manage', ['set_name' => $validated['set_name']])
                ->with('error', 'Nothing to print — add plates to this set in your collection first.');
        }

        $ownedPlateCount = $collectionByPlateId->filter(
            fn (CollectionItem $item) => ! $item->is_wanted && $item->ownedItemCount() > 0
        )->count();

        $wantedCount = $collectionByPlateId->filter(
            fn (CollectionItem $item) => $item->is_wanted
        )->count();

        $setCatalogTotal = $this->catalogTotalForSet($setData['plates'], $collectionByPlateId);

        $pdf = Pdf::loadView('collection.pdf.set-checklist', [
            'setMeta' => $setData['setMeta'],
            'plates' => $plates,
            'collectionByPlateId' => $collectionByPlateId,
            'scope' => $scope,
            'user' => Auth::user(),
            'generatedAt' => now(),
            'ownedPlateCount' => $ownedPlateCount,
            'wantedCount' => $wantedCount,
            'totalInSet' => $setData['plates']->count(),
            'setCatalogTotal' => $setCatalogTotal,
            'usesCatalogCardNumbers' => $setData['usesCatalogCardNumbers'],
        ])->setPaper('letter', 'portrait');

        $filename = Str::slug($setData['setMeta']->set_name)
            . '-'
            . ($scope === 'mine' ? 'my-collection' : 'checklist')
            . '-'
            . Auth::user()->username
            . '.pdf';

        return $pdf->download($filename);
    }

    public function reportsIndex()
    {
        $missingFilterOptions = $this->missingReportFilterOptions(Auth::id());
        $wantFilterOptions = $this->wantReportFilterOptions(Auth::id());

        return view('collection.reports.index', [
            'setNames' => $this->catalogSetNames(),
            'reportDecades' => $missingFilterOptions['decades'],
            'reportSets' => $missingFilterOptions['sets'],
            'reportJurisdictions' => $missingFilterOptions['jurisdictions'],
            'wantReportDecades' => $wantFilterOptions['decades'],
            'wantReportSets' => $wantFilterOptions['sets'],
            'wantReportJurisdictions' => $wantFilterOptions['jurisdictions'],
        ]);
    }

    public function setInventoryReport(Request $request)
    {
        $validated = $request->validate([
            'set_name' => ['required', 'string', 'max:255'],
        ]);

        $setData = $this->resolveSetCollectionData($validated['set_name']);

        if ($setData === null) {
            return redirect()
                ->route('collection.reports.index')
                ->with('error', 'Set not found. Choose a set from the list.');
        }

        $plates = $setData['plates'];
        $collectionByPlateId = $setData['collectionByPlateId'];

        $haveCount = 0;
        $missingCount = 0;
        $wantedCount = 0;

        foreach ($plates as $plate) {
            $entry = $collectionByPlateId->get($plate->id);

            if ($entry && ! $entry->is_wanted && $entry->ownedItemCount() > 0) {
                $haveCount++;
            } elseif ($entry?->is_wanted) {
                $wantedCount++;
            } else {
                $missingCount++;
            }
        }

        $setCatalogTotal = $this->catalogTotalForSet($plates, $collectionByPlateId);

        return view('collection.reports.set-inventory', [
            'setMeta' => $setData['setMeta'],
            'plates' => $plates,
            'collectionByPlateId' => $collectionByPlateId,
            'haveCount' => $haveCount,
            'missingCount' => $missingCount,
            'wantedCount' => $wantedCount,
            'totalInSet' => $plates->count(),
            'setCatalogTotal' => $setCatalogTotal,
            'generatedAt' => now(),
        ]);
    }

    public function missingItemsReport(Request $request)
    {
        $validated = $request->validate([
            'scope' => ['required', Rule::in(['all', 'decade', 'set', 'jurisdiction'])],
            'decade' => ['nullable', 'integer', 'min:1000', 'max:9990'],
            'set_name' => ['nullable', 'string', 'max:255'],
            'jurisdiction' => ['nullable', 'string', 'max:64'],
        ]);

        $scope = $validated['scope'];
        $filterOptions = $this->missingReportFilterOptions(Auth::id());

        if ($filterOptions['sets']->isEmpty()) {
            return redirect()
                ->route('collection.reports.index')
                ->withInput()
                ->with('error', 'Record at least one owned item in a set before running this report.');
        }

        if ($scope === 'decade') {
            if ($validated['decade'] === null || ! in_array((int) $validated['decade'], $filterOptions['decades']->all(), true)) {
                return redirect()
                    ->route('collection.reports.index')
                    ->withInput()
                    ->with('error', 'Choose a valid decade for this report.');
            }
        }

        if ($scope === 'set') {
            $validSetNames = $filterOptions['sets']->pluck('set_name')->all();
            if ($validated['set_name'] === null || ! in_array($validated['set_name'], $validSetNames, true)) {
                return redirect()
                    ->route('collection.reports.index')
                    ->withInput()
                    ->with('error', 'Choose a valid set for this report.');
            }
        }

        if ($scope === 'jurisdiction') {
            $validJurisdictions = $filterOptions['jurisdictions']->all();
            if ($validated['jurisdiction'] === null || ! in_array($validated['jurisdiction'], $validJurisdictions, true)) {
                return redirect()
                    ->route('collection.reports.index')
                    ->withInput()
                    ->with('error', 'Choose a valid jurisdiction for this report.');
            }
        }

        $rows = $this->missingItemsForUser(
            Auth::id(),
            $scope,
            isset($validated['decade']) ? (int) $validated['decade'] : null,
            $validated['set_name'] ?? null,
            $validated['jurisdiction'] ?? null
        );

        return view('collection.reports.missing-items', [
            'rows' => $rows,
            'scope' => $scope,
            'scopeLabel' => $this->reportScopeLabel(
                $scope,
                $validated['decade'] ?? null,
                $validated['set_name'] ?? null,
                $validated['jurisdiction'] ?? null
            ),
            'qualifyingSetCount' => $filterOptions['sets']->count(),
            'generatedAt' => now(),
        ]);
    }

    public function wantListReport(Request $request)
    {
        $validated = $request->validate([
            'scope' => ['required', Rule::in(['all', 'decade', 'set', 'jurisdiction'])],
            'decade' => ['nullable', 'integer', 'min:1000', 'max:9990'],
            'set_name' => ['nullable', 'string', 'max:255'],
            'jurisdiction' => ['nullable', 'string', 'max:64'],
        ]);

        $scope = $validated['scope'];
        $filterOptions = $this->wantReportFilterOptions(Auth::id());

        if ($filterOptions['sets']->isEmpty()) {
            return redirect()
                ->route('collection.reports.index')
                ->withInput()
                ->with('error', 'Mark at least one plate on your want list before running this report.');
        }

        if ($scope === 'decade') {
            if ($validated['decade'] === null || ! in_array((int) $validated['decade'], $filterOptions['decades']->all(), true)) {
                return redirect()
                    ->route('collection.reports.index')
                    ->withInput()
                    ->with('error', 'Choose a valid decade for this report.');
            }
        }

        if ($scope === 'set') {
            $validSetNames = $filterOptions['sets']->pluck('set_name')->all();
            if ($validated['set_name'] === null || ! in_array($validated['set_name'], $validSetNames, true)) {
                return redirect()
                    ->route('collection.reports.index')
                    ->withInput()
                    ->with('error', 'Choose a valid set for this report.');
            }
        }

        if ($scope === 'jurisdiction') {
            $validJurisdictions = $filterOptions['jurisdictions']->all();
            if ($validated['jurisdiction'] === null || ! in_array($validated['jurisdiction'], $validJurisdictions, true)) {
                return redirect()
                    ->route('collection.reports.index')
                    ->withInput()
                    ->with('error', 'Choose a valid jurisdiction for this report.');
            }
        }

        $rows = $this->wantListItemsForUser(
            Auth::id(),
            $scope,
            isset($validated['decade']) ? (int) $validated['decade'] : null,
            $validated['set_name'] ?? null,
            $validated['jurisdiction'] ?? null
        );

        return view('collection.reports.want-list', [
            'rows' => $rows,
            'scope' => $scope,
            'scopeLabel' => $this->reportScopeLabel(
                $scope,
                $validated['decade'] ?? null,
                $validated['set_name'] ?? null,
                $validated['jurisdiction'] ?? null,
                'All want list items'
            ),
            'wantListSetCount' => $filterOptions['sets']->count(),
            'generatedAt' => now(),
        ]);
    }

    /**
     * @return array{decades: Collection<int, int>, sets: Collection<int, object>, jurisdictions: Collection<int, string>}
     */
    private function missingReportFilterOptions(int $userId): array
    {
        return $this->reportFilterOptionsForSetCodes($this->qualifyingSetCodesForUser($userId));
    }

    /**
     * @return array{decades: Collection<int, int>, sets: Collection<int, object>, jurisdictions: Collection<int, string>}
     */
    private function wantReportFilterOptions(int $userId): array
    {
        $wantedPlateIds = DB::table('collection_items')
            ->where('user_id', $userId)
            ->where('is_wanted', true)
            ->pluck('plate_id');

        if ($wantedPlateIds->isEmpty()) {
            return [
                'decades' => collect(),
                'sets' => collect(),
                'jurisdictions' => collect(),
            ];
        }

        $sets = DB::table('plates')
            ->whereIn('id', $wantedPlateIds)
            ->select('set_name', DB::raw('MAX(set_code) as set_code'), DB::raw('MIN(year) as year'), DB::raw('COUNT(*) as plate_count'))
            ->groupBy('set_name')
            ->orderBy('set_name')
            ->get();

        $plateMeta = DB::table('plates')
            ->whereIn('id', $wantedPlateIds)
            ->select('year', 'jurisdiction')
            ->get();

        $decades = $plateMeta
            ->pluck('year')
            ->filter(static fn ($year) => $year !== null && $year !== '')
            ->map(static fn ($year) => (int) (floor(((int) $year) / 10) * 10))
            ->unique()
            ->sort()
            ->values();

        $jurisdictions = $plateMeta
            ->pluck('jurisdiction')
            ->filter(static fn ($value) => $value !== null && $value !== '')
            ->map(static fn ($value) => (string) $value)
            ->unique()
            ->sort(static fn ($a, $b) => strcasecmp($a, $b))
            ->values();

        return [
            'decades' => $decades,
            'sets' => $sets,
            'jurisdictions' => $jurisdictions,
        ];
    }

    /**
     * @return array{decades: Collection<int, int>, sets: Collection<int, object>, jurisdictions: Collection<int, string>}
     */
    private function reportFilterOptionsForSetCodes(array $setCodes): array
    {
        if ($setCodes === []) {
            return [
                'decades' => collect(),
                'sets' => collect(),
                'jurisdictions' => collect(),
            ];
        }

        $sets = DB::table('plates')
            ->select('set_name', DB::raw('MAX(set_code) as set_code'), DB::raw('MIN(year) as year'), DB::raw('COUNT(*) as plate_count'))
            ->whereIn('set_code', $setCodes)
            ->groupBy('set_name')
            ->orderBy('set_name')
            ->get();

        $plateMeta = DB::table('plates')
            ->whereIn('set_code', $setCodes)
            ->whereNotNull('year')
            ->select('year', 'jurisdiction')
            ->get();

        $decades = $plateMeta
            ->pluck('year')
            ->map(static fn ($year) => (int) (floor(((int) $year) / 10) * 10))
            ->unique()
            ->sort()
            ->values();

        $jurisdictions = $plateMeta
            ->pluck('jurisdiction')
            ->filter(static fn ($value) => $value !== null && $value !== '')
            ->map(static fn ($value) => (string) $value)
            ->unique()
            ->sort(static fn ($a, $b) => strcasecmp($a, $b))
            ->values();

        return [
            'decades' => $decades,
            'sets' => $sets,
            'jurisdictions' => $jurisdictions,
        ];
    }

    /**
     * @return list<string>
     */
    private function qualifyingSetCodesForUser(int $userId): array
    {
        return DB::table('collection_owned_items')
            ->join('collection_items', 'collection_items.id', '=', 'collection_owned_items.collection_item_id')
            ->join('plates', 'plates.id', '=', 'collection_items.plate_id')
            ->where('collection_items.user_id', $userId)
            ->where('collection_items.is_wanted', false)
            ->distinct()
            ->pluck('plates.set_code')
            ->all();
    }

    /**
     * @return Collection<int, object{plate: Plate, entry: CollectionItem|null, onWantList: bool}>
     */
    private function missingItemsForUser(
        int $userId,
        string $scope,
        ?int $decade,
        ?string $setName,
        ?string $jurisdiction
    ): Collection {
        $qualifyingSetCodes = $this->qualifyingSetCodesForUser($userId);

        if ($qualifyingSetCodes === []) {
            return collect();
        }

        $platesQuery = Plate::query()
            ->whereIn('set_code', $qualifyingSetCodes)
            ->orderBy('set_name')
            ->orderedForCatalog();

        if ($scope === 'decade' && $decade !== null) {
            $platesQuery->whereBetween('year', [$decade, $decade + 9]);
        }

        if ($scope === 'set' && $setName !== null) {
            $platesQuery->where('set_name', $setName);
        }

        if ($scope === 'jurisdiction' && $jurisdiction !== null) {
            $platesQuery->where('jurisdiction', $jurisdiction);
        }

        $plates = $platesQuery->get();

        if ($plates->isEmpty()) {
            return collect();
        }

        $collectionByPlateId = CollectionItem::query()
            ->with('ownedItems')
            ->where('user_id', $userId)
            ->whereIn('plate_id', $plates->pluck('id'))
            ->get()
            ->keyBy('plate_id');

        return $plates
            ->filter(static function (Plate $plate) use ($collectionByPlateId) {
                $entry = $collectionByPlateId->get($plate->id);

                return $entry === null || $entry->ownedItemCount() === 0;
            })
            ->values()
            ->map(static function (Plate $plate) use ($collectionByPlateId) {
                $entry = $collectionByPlateId->get($plate->id);

                return (object) [
                    'plate' => $plate,
                    'entry' => $entry,
                    'onWantList' => (bool) ($entry?->is_wanted),
                ];
            });
    }

    /**
     * @return Collection<int, object{plate: Plate, entry: CollectionItem, notes: string|null}>
     */
    private function wantListItemsForUser(
        int $userId,
        string $scope,
        ?int $decade,
        ?string $setName,
        ?string $jurisdiction
    ): Collection {
        $query = CollectionItem::query()
            ->with(['plate'])
            ->where('user_id', $userId)
            ->where('is_wanted', true)
            ->whereHas('plate', function ($plateQuery) use ($scope, $decade, $setName, $jurisdiction) {
                if ($scope === 'decade' && $decade !== null) {
                    $plateQuery->whereBetween('year', [$decade, $decade + 9]);
                }

                if ($scope === 'set' && $setName !== null) {
                    $plateQuery->where('set_name', $setName);
                }

                if ($scope === 'jurisdiction' && $jurisdiction !== null) {
                    $plateQuery->where('jurisdiction', $jurisdiction);
                }
            });

        return $query
            ->get()
            ->sortBy(static function (CollectionItem $item) {
                $plate = $item->plate;

                return [
                    $plate->set_name,
                    $plate->sort_order ?? 0,
                    $plate->variety_key ?? '',
                    $plate->id,
                ];
            })
            ->values()
            ->map(static function (CollectionItem $item) {
                return (object) [
                    'plate' => $item->plate,
                    'entry' => $item,
                    'notes' => $item->notes,
                ];
            });
    }

    private function reportScopeLabel(
        string $scope,
        ?int $decade,
        ?string $setName,
        ?string $jurisdiction,
        string $allLabel = 'All qualifying sets'
    ): string {
        return match ($scope) {
            'decade' => $decade !== null ? $decade.'s' : 'Decade',
            'set' => $setName ?? 'Set',
            'jurisdiction' => $jurisdiction !== null ? strtoupper($jurisdiction) : 'Jurisdiction',
            default => $allLabel,
        };
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function catalogSetNames()
    {
        return DB::table('plates')
            ->select('set_name', DB::raw('MAX(company) as company'), DB::raw('MIN(year) as year'), DB::raw('COUNT(*) as plate_count'))
            ->groupBy('set_name')
            ->orderBy('set_name')
            ->get();
    }

    /**
     * @return array{setMeta: object, plates: Collection<int, Plate>, collectionByPlateId: Collection<int, CollectionItem>}|null
     */
    private function resolveSetCollectionData(string $setName): ?array
    {
        $setMeta = DB::table('plates')
            ->select(
                'set_code',
                DB::raw('MAX(set_name) as set_name'),
                DB::raw('MAX(company) as company'),
                DB::raw('MIN(year) as year'),
                DB::raw('COUNT(*) as plate_count')
            )
            ->where('set_name', $setName)
            ->groupBy('set_code')
            ->first();

        if (! $setMeta) {
            return null;
        }

        $plates = Plate::query()
            ->where('set_name', $setName)
            ->orderedForCatalog()
            ->get();

        $collectionByPlateId = Auth::user()
            ->collectionItems()
            ->with('ownedItems')
            ->whereIn('plate_id', $plates->pluck('id'))
            ->get()
            ->keyBy('plate_id');

        return [
            'setMeta' => $setMeta,
            'plates' => $plates,
            'collectionByPlateId' => $collectionByPlateId,
            'usesCatalogCardNumbers' => Plate::platesUseCatalogCardNumbers($plates),
        ];
    }

    public function updateManage(Request $request)
    {
        $validated = $request->validate(array_merge([
            'set_name' => ['required', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.is_wanted' => ['nullable', 'boolean'],
            'items.*.notes' => ['nullable', 'string', 'max:5000'],
            'items.*.storage_location' => ['nullable', 'string', 'max:128'],
        ], $this->ownedItemFieldRules('items.*.owned_items')));

        $setName = $validated['set_name'];
        $validPlateIds = Plate::query()
            ->where('set_name', $setName)
            ->pluck('id')
            ->all();

        if ($validPlateIds === []) {
            return back()->with('error', 'Set not found.');
        }

        $items = $validated['items'] ?? [];
        $saved = 0;
        $removed = 0;

        $existingByPlateId = CollectionItem::query()
            ->where('user_id', Auth::id())
            ->whereIn('plate_id', $validPlateIds)
            ->get()
            ->keyBy('plate_id');

        foreach ($validPlateIds as $plateId) {
            $row = $items[$plateId] ?? [];
            $existing = $existingByPlateId->get($plateId);
            $result = $this->persistManagePlateRow(Auth::id(), $plateId, $row, $existing);

            if ($result === 'saved') {
                $saved++;
            } elseif ($result === 'removed') {
                $removed++;
            }
        }

        $message = "Set saved — {$saved} " . ($saved === 1 ? 'entry' : 'entries') . ' updated';
        if ($removed > 0) {
            $message .= ", {$removed} removed";
        }
        $message .= '.';

        return redirect()
            ->route('collection.manage', ['set_name' => $setName])
            ->with('success', $message);
    }

    public function updateManageRow(Request $request)
    {
        $plateId = (int) $request->input('plate_id');

        $validated = $request->validate(array_merge([
            'set_name' => ['required', 'string', 'max:255'],
            'plate_id' => ['required', 'integer'],
            "items.{$plateId}.is_wanted" => ['nullable', 'boolean'],
            "items.{$plateId}.notes" => ['nullable', 'string', 'max:5000'],
            "items.{$plateId}.storage_location" => ['nullable', 'string', 'max:128'],
        ], $this->ownedItemFieldRules("items.{$plateId}.owned_items")));

        $setName = $validated['set_name'];

        $plate = Plate::query()
            ->where('set_name', $setName)
            ->where('id', $plateId)
            ->first();

        if ($plate === null) {
            $message = 'That plate is not in this set.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $existing = CollectionItem::query()
            ->where('user_id', Auth::id())
            ->where('plate_id', $plateId)
            ->first();

        $items = $request->input('items', []);
        $row = is_array($items[$plateId] ?? null) ? $items[$plateId] : [];
        $result = $this->persistManagePlateRow(Auth::id(), $plateId, $row, $existing);

        $label = $plate->jurisdiction ? strtoupper($plate->jurisdiction) : 'Plate';
        $message = $result === 'removed'
            ? "{$label} removed from your collection."
            : "{$label} saved.";

        if ($request->expectsJson()) {
            $item = CollectionItem::query()
                ->where('user_id', Auth::id())
                ->where('plate_id', $plateId)
                ->with('ownedItems')
                ->first();

            $valueLabel = '--';
            $hasEntry = $item !== null;

            if ($item && ! $item->is_wanted) {
                $item->setRelation('plate', $plate);
                $valueLabel = $item->formattedOwnedLineValue();
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'value_label' => $valueLabel,
                'has_entry' => $hasEntry,
            ]);
        }

        return redirect()
            ->route('collection.manage', ['set_name' => $setName])
            ->with('success', $message);
    }

    public function fillManageSet(Request $request)
    {
        $validated = $request->validate([
            'set_name' => ['required', 'string', 'max:255'],
            'item_count' => ['required', 'integer', 'min:1', 'max:9999'],
            'grade' => ['nullable', Rule::in(CollectionItem::GRADE_CODES)],
            'mode' => ['required', Rule::in(['empty', 'all'])],
            'storage_location' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $setData = $this->resolveSetCollectionData($validated['set_name']);

        if ($setData === null) {
            return redirect()
                ->route('collection.manage')
                ->with('error', 'Set not found.');
        }

        $filled = 0;
        $ownedItemPayload = [
            'grade' => $validated['grade'] ?? null,
            'storage_location' => CollectionItem::normalizeOwnedItemPayload([
                'storage_location' => $validated['storage_location'] ?? null,
            ])['storage_location'],
        ];
        $ownedItemRows = [];
        for ($i = 0; $i < $validated['item_count']; $i++) {
            $ownedItemRows[] = $ownedItemPayload;
        }
        $entryNotes = CollectionItem::normalizeOwnedItemPayload([
            'notes' => $validated['notes'] ?? null,
        ])['notes'];

        foreach ($setData['plates'] as $plate) {
            $existing = $setData['collectionByPlateId'][$plate->id] ?? null;

            if ($validated['mode'] === 'empty' && ! $this->rowIsEmptyForFill($existing)) {
                continue;
            }

            if ($existing) {
                $item = $existing;
                $item->update([
                    'is_wanted' => false,
                    'notes' => $entryNotes,
                ]);
            } else {
                $item = CollectionItem::create([
                    'user_id' => Auth::id(),
                    'plate_id' => $plate->id,
                    'is_wanted' => false,
                    'notes' => $entryNotes,
                ]);
            }

            $item->syncOwnedItems($ownedItemRows);
            $filled++;
        }

        $message = $validated['mode'] === 'empty'
            ? "Filled {$filled} empty rows with your defaults."
            : "Applied defaults to all {$filled} rows in this set.";

        return redirect()
            ->route('collection.manage', ['set_name' => $validated['set_name']])
            ->with('success', $message . ' Adjust any exceptions below if needed.');
    }

    private function rowIsEmptyForFill(?CollectionItem $existing): bool
    {
        if ($existing === null) {
            return true;
        }

        if ($existing->is_wanted) {
            return false;
        }

        return $existing->ownedItemCount() === 0;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_id' => ['required', 'integer', 'exists:plates,id'],
            'grade' => ['nullable', Rule::in(CollectionItem::GRADE_CODES)],
            'is_wanted' => ['sometimes', 'boolean'],
        ]);

        $isWanted = $request->boolean('is_wanted');
        $existing = CollectionItem::query()
            ->where('user_id', Auth::id())
            ->where('plate_id', $validated['plate_id'])
            ->first();

        if ($existing) {
            return back()->with('error', 'That plate is already in your collection. Edit it from My Collection.');
        }

        $item = CollectionItem::create([
            'user_id' => Auth::id(),
            'plate_id' => $validated['plate_id'],
            'is_wanted' => $isWanted,
        ]);

        if (! $isWanted) {
            $item->syncOwnedItems([[
                'grade' => $validated['grade'] ?? null,
            ]]);
        }

        $message = $isWanted
            ? 'Added to your want list.'
            : 'Added to your collection.';

        return back()->with('success', $message);
    }

    public function edit(CollectionItem $collectionItem)
    {
        $this->authorizeItem($collectionItem);

        $collectionItem->load(['plate', 'ownedItems']);

        $wantListMatches = \App\Support\CollectionWantListMatcher::matchesForPlate(
            $collectionItem->plate_id,
            Auth::id()
        );

        return view('collection.edit', [
            'item' => $collectionItem,
            'grades' => CollectionItem::GRADES,
            'listingTypes' => CollectionOwnedItem::LISTING_TYPES,
            'wantListMatches' => $wantListMatches,
        ]);
    }

    public function update(Request $request, CollectionItem $collectionItem)
    {
        $this->authorizeItem($collectionItem);

        $validated = $request->validate(array_merge([
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_wanted' => ['sometimes', 'boolean'],
        ], $this->ownedItemFieldRules('owned_items')));

        $isWanted = $request->boolean('is_wanted');

        $collectionItem->update([
            'notes' => $validated['notes'] ?? null,
            'is_wanted' => $isWanted,
        ]);

        if ($isWanted) {
            $collectionItem->ownedItems()->delete();
        } else {
            $collectionItem->syncOwnedItems($validated['owned_items'] ?? []);
        }

        return redirect()
            ->route('collection.index')
            ->with('success', 'Collection entry updated.');
    }

    public function destroy(CollectionItem $collectionItem)
    {
        $this->authorizeItem($collectionItem);

        $collectionItem->delete();

        return redirect()
            ->route('collection.index')
            ->with('success', 'Removed from your collection.');
    }

    private function authorizeItem(CollectionItem $collectionItem): void
    {
        if ($collectionItem->user_id !== Auth::id()) {
            abort(403);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return 'saved'|'removed'|'unchanged'
     */
    private function persistManagePlateRow(int $userId, int $plateId, array $row, ?CollectionItem $existing): string
    {
        $isWanted = ! empty($row['is_wanted']);
        $ownedItemRows = $row['owned_items'] ?? [];
        $rowStorage = trim((string) ($row['storage_location'] ?? ''));

        if ($rowStorage !== '') {
            foreach ($ownedItemRows as $index => $ownedRow) {
                if (! is_array($ownedRow)) {
                    continue;
                }

                $ownedItemRows[$index]['storage_location'] = $rowStorage;
            }
        }

        $hasOwnedItems = $this->submittedOwnedItemsHaveContent($ownedItemRows);
        $shouldKeep = $isWanted || $hasOwnedItems;

        if (! $shouldKeep) {
            if ($existing) {
                $existing->delete();

                return 'removed';
            }

            return 'unchanged';
        }

        if ($existing) {
            $item = $existing;
            $item->update([
                'is_wanted' => $isWanted,
                'notes' => $row['notes'] ?? null,
            ]);
        } else {
            $item = CollectionItem::create([
                'user_id' => $userId,
                'plate_id' => $plateId,
                'is_wanted' => $isWanted,
                'notes' => $row['notes'] ?? null,
            ]);
        }

        if ($isWanted) {
            $item->ownedItems()->delete();
        } else {
            $item->syncOwnedItems($ownedItemRows);
        }

        return 'saved';
    }

    /**
     * @return array<string, mixed>
     */
    private function ownedItemFieldRules(string $prefix): array
    {
        return [
            $prefix => ['nullable', 'array'],
            "{$prefix}.*.grade" => ['nullable', Rule::in(CollectionItem::GRADE_CODES)],
            "{$prefix}.*.acquired_date" => ['nullable', 'date'],
            "{$prefix}.*.price_paid" => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            "{$prefix}.*.storage_location" => ['nullable', 'string', 'max:128'],
            "{$prefix}.*.notes" => ['nullable', 'string', 'max:5000'],
            "{$prefix}.*.listing_type" => ['nullable', Rule::in(['sale', 'trade', 'both'])],
            "{$prefix}.*.listing_notes" => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @param  array<int, mixed>|null  $rows
     */
    private function submittedOwnedItemsHaveContent(?array $rows): bool
    {
        if (! is_array($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (CollectionItem::ownedItemPayloadHasContent(CollectionItem::normalizeOwnedItemPayload($row))) {
                return true;
            }
        }

        return false;
    }
}

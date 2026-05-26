<?php

namespace App\Http\Controllers;

use App\Models\CollectionItem;
use App\Models\Plate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'owned');
        if (! in_array($filter, ['owned', 'wanted', 'all'], true)) {
            $filter = 'owned';
        }

        $query = Auth::user()
            ->collectionItems()
            ->with('plate')
            ->join('plates', 'plates.id', '=', 'collection_items.plate_id')
            ->select('collection_items.*');

        if ($filter === 'owned') {
            $query->where('collection_items.is_wanted', false);
        } elseif ($filter === 'wanted') {
            $query->where('collection_items.is_wanted', true);
        }

        $items = $query
            ->orderBy('plates.set_name')
            ->orderBy('plates.jurisdiction')
            ->paginate(24)
            ->appends(['filter' => $filter]);

        $stats = [
            'owned' => Auth::user()->collectionItems()->where('is_wanted', false)->sum('quantity'),
            'wanted' => Auth::user()->collectionItems()->where('is_wanted', true)->count(),
            'distinct_owned' => Auth::user()->collectionItems()->where('is_wanted', false)->count(),
        ];

        return view('collection.index', [
            'items' => $items,
            'filter' => $filter,
            'stats' => $stats,
        ]);
    }

    public function manage(Request $request)
    {
        $setNames = DB::table('plates')
            ->select('set_name', DB::raw('MAX(company) as company'), DB::raw('MIN(year) as year'), DB::raw('COUNT(*) as plate_count'))
            ->groupBy('set_name')
            ->orderBy('set_name')
            ->get();

        $setName = $request->query('set_name');

        if ($setName === null || $setName === '') {
            return view('collection.manage', [
                'setNames' => $setNames,
                'selectedSet' => null,
                'setMeta' => null,
                'plates' => null,
                'collectionByPlateId' => collect(),
                'conditions' => CollectionItem::CONDITIONS,
            ]);
        }

        $setData = $this->resolveSetCollectionData($setName);

        if ($setData === null) {
            return redirect()
                ->route('collection.manage')
                ->with('error', 'Set not found. Choose a set from the list.');
        }

        return view('collection.manage', [
            'setNames' => $setNames,
            'selectedSet' => $setName,
            'setMeta' => $setData['setMeta'],
            'plates' => $setData['plates'],
            'collectionByPlateId' => $setData['collectionByPlateId'],
            'conditions' => CollectionItem::CONDITIONS,
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
            fn (CollectionItem $item) => ! $item->is_wanted && $item->quantity > 0
        )->count();

        $wantedCount = $collectionByPlateId->filter(
            fn (CollectionItem $item) => $item->is_wanted
        )->count();

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
        ])->setPaper('letter', 'portrait');

        $filename = Str::slug($setData['setMeta']->set_name)
            . '-'
            . ($scope === 'mine' ? 'my-collection' : 'checklist')
            . '-'
            . Auth::user()->username
            . '.pdf';

        return $pdf->download($filename);
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
            ->orderBy('sort_order')
            ->orderBy('jurisdiction')
            ->orderBy('variety_key')
            ->get();

        $collectionByPlateId = Auth::user()
            ->collectionItems()
            ->whereIn('plate_id', $plates->pluck('id'))
            ->get()
            ->keyBy('plate_id');

        return [
            'setMeta' => $setMeta,
            'plates' => $plates,
            'collectionByPlateId' => $collectionByPlateId,
        ];
    }

    public function updateManage(Request $request)
    {
        $validated = $request->validate([
            'set_name' => ['required', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'items.*.condition' => ['nullable', Rule::in(array_keys(CollectionItem::CONDITIONS))],
            'items.*.is_wanted' => ['nullable', 'boolean'],
            'items.*.notes' => ['nullable', 'string', 'max:5000'],
            'items.*.storage_location' => ['nullable', 'string', 'max:128'],
        ]);

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

        foreach ($validPlateIds as $plateId) {
            $row = $items[$plateId] ?? [];
            $quantity = isset($row['quantity']) && $row['quantity'] !== '' ? (int) $row['quantity'] : 0;
            $isWanted = ! empty($row['is_wanted']);
            $shouldKeep = $quantity > 0 || $isWanted;

            $existing = CollectionItem::query()
                ->where('user_id', Auth::id())
                ->where('plate_id', $plateId)
                ->first();

            if (! $shouldKeep) {
                if ($existing) {
                    $existing->delete();
                    $removed++;
                }

                continue;
            }

            $payload = [
                'user_id' => Auth::id(),
                'plate_id' => $plateId,
                'quantity' => $quantity > 0 ? $quantity : 1,
                'condition' => $row['condition'] ?? null,
                'is_wanted' => $isWanted,
                'notes' => $row['notes'] ?? null,
                'storage_location' => $row['storage_location'] ?? null,
            ];

            if ($existing) {
                $existing->update($payload);
            } else {
                CollectionItem::create($payload);
            }

            $saved++;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_id' => ['required', 'integer', 'exists:plates,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'condition' => ['nullable', Rule::in(array_keys(CollectionItem::CONDITIONS))],
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

        CollectionItem::create([
            'user_id' => Auth::id(),
            'plate_id' => $validated['plate_id'],
            'quantity' => $validated['quantity'],
            'condition' => $validated['condition'] ?? null,
            'is_wanted' => $isWanted,
        ]);

        $message = $isWanted
            ? 'Added to your want list.'
            : 'Added to your collection.';

        return back()->with('success', $message);
    }

    public function edit(CollectionItem $collectionItem)
    {
        $this->authorizeItem($collectionItem);

        $collectionItem->load('plate');

        return view('collection.edit', [
            'item' => $collectionItem,
            'conditions' => CollectionItem::CONDITIONS,
        ]);
    }

    public function update(Request $request, CollectionItem $collectionItem)
    {
        $this->authorizeItem($collectionItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'condition' => ['nullable', Rule::in(array_keys(CollectionItem::CONDITIONS))],
            'acquired_date' => ['nullable', 'date'],
            'price_paid' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'storage_location' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_wanted' => ['sometimes', 'boolean'],
        ]);

        $collectionItem->update([
            'quantity' => $validated['quantity'],
            'condition' => $validated['condition'] ?? null,
            'acquired_date' => $validated['acquired_date'] ?? null,
            'price_paid' => $validated['price_paid'] ?? null,
            'storage_location' => $validated['storage_location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_wanted' => $request->boolean('is_wanted'),
        ]);

        return redirect()
            ->route('collection.index', ['filter' => $collectionItem->is_wanted ? 'wanted' : 'owned'])
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
}

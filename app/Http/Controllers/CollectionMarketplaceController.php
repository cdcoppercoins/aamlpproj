<?php

namespace App\Http\Controllers;

use App\Models\CollectionItem;
use App\Models\CollectionOwnedItem;
use App\Support\CollectionWantListMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionMarketplaceController extends Controller
{
    public function index()
    {
        $viewerId = Auth::id();

        $listings = CollectionOwnedItem::query()
            ->whereNotNull('listing_type')
            ->whereIn('listing_type', ['sale', 'trade', 'both'])
            ->whereHas('collectionItem', function ($query) use ($viewerId) {
                $query->where('is_wanted', false)
                    ->where('user_id', '!=', $viewerId);
            })
            ->with([
                'collectionItem.user',
                'collectionItem.plate',
            ])
            ->get()
            ->sortBy([
                fn (CollectionOwnedItem $item) => strtolower($item->collectionItem->user->username ?? ''),
                fn (CollectionOwnedItem $item) => strtolower($item->collectionItem->plate->set_name ?? ''),
                fn (CollectionOwnedItem $item) => $item->collectionItem->plate->sort_order ?? 0,
            ])
            ->values();

        $plateIds = $listings
            ->map(fn (CollectionOwnedItem $item) => $item->collectionItem->plate_id)
            ->unique()
            ->all();

        $viewerWantedPlateIds = CollectionItem::query()
            ->where('user_id', $viewerId)
            ->where('is_wanted', true)
            ->whereIn('plate_id', $plateIds)
            ->pluck('plate_id')
            ->flip();

        $wantMatchCounts = CollectionWantListMatcher::matchCountsByPlate($plateIds, $viewerId);

        return view('collection.marketplace.index', [
            'listings' => $listings,
            'viewerWantedPlateIds' => $viewerWantedPlateIds,
            'wantMatchCounts' => $wantMatchCounts,
        ]);
    }

    public function wantMatches(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plate_id' => ['required', 'integer', 'exists:plates,id'],
        ]);

        $matches = CollectionWantListMatcher::matchesForPlate(
            (int) $validated['plate_id'],
            Auth::id()
        );

        return response()->json([
            'count' => $matches->count(),
            'matches' => $matches->map(fn ($row) => [
                'username' => $row->username,
                'name' => $row->name,
            ])->values(),
        ]);
    }

    public function myListings()
    {
        $listings = CollectionOwnedItem::query()
            ->whereNotNull('listing_type')
            ->whereIn('listing_type', ['sale', 'trade', 'both'])
            ->whereHas('collectionItem', function ($query) {
                $query->where('user_id', Auth::id())
                    ->where('is_wanted', false);
            })
            ->with(['collectionItem.plate'])
            ->orderByDesc('updated_at')
            ->get();

        $plateIds = $listings
            ->map(fn (CollectionOwnedItem $item) => $item->collectionItem->plate_id)
            ->unique()
            ->all();

        $wantMatchesByPlate = [];
        foreach ($plateIds as $plateId) {
            $wantMatchesByPlate[$plateId] = CollectionWantListMatcher::matchesForPlate($plateId, Auth::id());
        }

        return view('collection.marketplace.my-listings', [
            'listings' => $listings,
            'wantMatchesByPlate' => $wantMatchesByPlate,
        ]);
    }
}

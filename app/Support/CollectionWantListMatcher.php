<?php

namespace App\Support;

use App\Models\CollectionItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CollectionWantListMatcher
{
    /**
     * Members who have this catalog plate on their want list (excluding one user).
     *
     * @return Collection<int, object{user_id: int, username: string, name: string}>
     */
    public static function matchesForPlate(int $plateId, int $excludeUserId): Collection
    {
        return CollectionItem::query()
            ->where('plate_id', $plateId)
            ->where('is_wanted', true)
            ->where('user_id', '!=', $excludeUserId)
            ->join('users', 'users.id', '=', 'collection_items.user_id')
            ->orderBy('users.username')
            ->get([
                'users.id as user_id',
                'users.username',
                'users.name',
            ]);
    }

    /**
     * @param  list<int>  $plateIds
     * @return array<int, int> plate_id => match count
     */
    public static function matchCountsByPlate(array $plateIds, int $excludeUserId): array
    {
        if ($plateIds === []) {
            return [];
        }

        return CollectionItem::query()
            ->whereIn('plate_id', $plateIds)
            ->where('is_wanted', true)
            ->where('user_id', '!=', $excludeUserId)
            ->selectRaw('plate_id, COUNT(*) as match_count')
            ->groupBy('plate_id')
            ->pluck('match_count', 'plate_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}

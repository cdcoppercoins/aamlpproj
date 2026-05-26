<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->orderByDesc('created_at');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        match ($request->query('status')) {
            'admin' => $query->where('is_admin', true),
            'blocked' => $query->where('is_blocked', true),
            'active' => $query->where('is_blocked', false),
            default => null,
        };

        $users = $query
            ->withCount('collectionItems')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function show(User $user): View
    {
        $user->loadCount('collectionItems');

        $collectionStats = DB::table('collection_items')
            ->where('user_id', $user->id)
            ->selectRaw('COUNT(*) as entry_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_wanted = 0 THEN quantity ELSE 0 END), 0) as owned_qty')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_wanted = 1 THEN quantity ELSE 0 END), 0) as wanted_qty')
            ->first();

        $setCount = (int) DB::table('collection_items')
            ->join('plates', 'plates.id', '=', 'collection_items.plate_id')
            ->where('collection_items.user_id', $user->id)
            ->selectRaw('COUNT(DISTINCT plates.set_name) as aggregate')
            ->value('aggregate');

        $publicSetCount = DB::table('collection_set_settings')
            ->where('user_id', $user->id)
            ->where('is_public', true)
            ->count();

        return view('admin.users.show', compact('user', 'collectionStats', 'setCount', 'publicSetCount'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_admin' => ['sometimes', 'boolean'],
            'is_blocked' => ['sometimes', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($user->id === Auth::id()) {
            if (array_key_exists('is_admin', $validated) && ! $validated['is_admin']) {
                return back()->with('error', 'You cannot remove your own administrator access.');
            }

            if (array_key_exists('is_blocked', $validated) && $validated['is_blocked']) {
                return back()->with('error', 'You cannot block your own account.');
            }
        }

        if (array_key_exists('is_admin', $validated) && ! $validated['is_admin'] && $user->is_admin) {
            $adminCount = User::query()->where('is_admin', true)->count();

            if ($adminCount <= 1) {
                return back()->with('error', 'At least one administrator must remain on the site.');
            }
        }

        $updates = [];

        if (array_key_exists('is_admin', $validated)) {
            $updates['is_admin'] = (bool) $validated['is_admin'];
        }

        if (array_key_exists('is_blocked', $validated)) {
            $blocked = (bool) $validated['is_blocked'];
            $updates['is_blocked'] = $blocked;
            $updates['blocked_at'] = $blocked ? now() : null;

            if (! $blocked) {
                $updates['blocked_reason'] = null;
            }
        }

        if (array_key_exists('blocked_reason', $validated) && ($updates['is_blocked'] ?? $user->is_blocked)) {
            $updates['blocked_reason'] = $validated['blocked_reason'] ?: null;
        }

        $user->fill($updates)->save();

        return back()->with('success', 'Member account updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account from the admin panel.');
        }

        if ($user->is_admin) {
            $adminCount = User::query()->where('is_admin', true)->count();

            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot delete the only administrator account.');
            }
        }

        $username = $user->username;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Deleted member account \"{$username}\" and all associated collection data.");
    }
}

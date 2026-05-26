<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectionItem;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::query()->count(),
            'admins' => User::query()->where('is_admin', true)->count(),
            'blocked_users' => User::query()->where('is_blocked', true)->count(),
            'plates' => DB::table('plates')->count(),
            'sets' => (int) DB::table('plates')->selectRaw('COUNT(DISTINCT set_name) as aggregate')->value('aggregate'),
            'collection_items' => CollectionItem::query()->count(),
            'newsletter_subscribers' => NewsletterSubscriber::query()->count(),
        ];

        $recentUsers = User::query()
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'username', 'email', 'is_admin', 'is_blocked', 'created_at']);

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }
}

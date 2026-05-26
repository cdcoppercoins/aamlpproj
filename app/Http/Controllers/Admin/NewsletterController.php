<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function index(Request $request): View
    {
        $query = NewsletterSubscriber::query()->orderByDesc('subscribed_at');

        if ($search = trim((string) $request->query('q'))) {
            $query->where('email', 'like', "%{$search}%");
        }

        $subscribers = $query->paginate(50)->withQueryString();

        return view('admin.newsletter.index', compact('subscribers', 'search'));
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $email = $subscriber->email;
        $subscriber->delete();

        return back()->with('success', "Removed newsletter subscriber {$email}.");
    }
}

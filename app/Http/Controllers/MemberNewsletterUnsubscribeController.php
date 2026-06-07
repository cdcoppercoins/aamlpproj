<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberNewsletterUnsubscribeController extends Controller
{
    public function show(Request $request, User $user): View
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This unsubscribe link is invalid or has expired.');
        }

        if ($user->member_newsletter_opt_out_at === null) {
            $user->member_newsletter_opt_out_at = now();
            $user->save();
        }

        return view('newsletter.member-unsubscribe', [
            'user' => $user,
        ]);
    }
}

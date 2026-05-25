<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsletterSubscriberController extends Controller
{
    public function store(Request $request)
    {
        if ($request->filled('company')) {
            return back()->with('newsletter_subscribed', 'Thank you. You are subscribed to our newsletter.');
        }

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email', ''))),
        ]);

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique('newsletter_subscribers', 'email'),
            ],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address with a working domain.',
            'email.unique' => 'That email address is already on our newsletter list.',
        ]);

        NewsletterSubscriber::create([
            'email' => $validated['email'],
            'subscribed_at' => now(),
        ]);

        return back()->with('newsletter_subscribed', 'Thank you. You are subscribed to our newsletter.');
    }
}

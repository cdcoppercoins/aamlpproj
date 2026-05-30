<?php

namespace App\Http\Controllers;

use App\Mail\ContributeMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContributeController extends Controller
{
    public function index()
    {
        return view('contribute');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // Honeypot check
        if ($request->filled('company')) {
            return redirect()->route('contribute')->with('success', 'Thank you! Your message has been sent.');
        }

        if (config('mail.default') === 'log' && app()->environment('production')) {
            return redirect()->route('contribute')
                ->with('error', 'Email is not configured on this server. Please email '.config('contribute.mail_to').' directly.')
                ->withInput();
        }

        try {
            Mail::to(config('contribute.mail_to'))->send(new ContributeMessage(
                senderName: $request->name,
                senderEmail: $request->email,
                messageText: $request->message,
                ip: $request->ip() ?? 'unknown',
            ));

            return redirect()->route('contribute')->with('success', 'Thank you! Your message has been sent.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('contribute')
                ->with('error', 'Mail failed on this server. Please email '.config('contribute.mail_to', 'cdcoppercoins@gmail.com').' directly.')
                ->withInput();
        }
    }
}

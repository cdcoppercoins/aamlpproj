<?php

namespace App\Http\Controllers;

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

        $to = 'cdcoppercoins@gmail.com';
        $subject = 'MiniLicensePlates.com Contribution';
        $body = "Name: {$request->name}\nEmail: {$request->email}\n\nMessage:\n{$request->message}\n\nIP: " . $request->ip();

        try {
            Mail::raw($body, function ($message) use ($to, $subject, $request) {
                $message->to($to)
                        ->subject($subject)
                        ->replyTo($request->email, $request->name);
            });

            return redirect()->route('contribute')->with('success', 'Thank you! Your message has been sent.');
        } catch (\Exception $e) {
            return redirect()->route('contribute')
                ->with('error', 'Mail failed on this server. Please email cdcoppercoins@gmail.com directly.')
                ->withInput();
        }
    }
}

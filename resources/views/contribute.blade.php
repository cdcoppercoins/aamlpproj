@extends('layouts.app')

@section('title', 'Contribute | MiniLicensePlates.com')

@section('content')
<div class="set-width">
    <h1>Contribute</h1>

    <p>
        Have an unlisted plate, a variety, better images, or historical information?
        Send the details below, or email <a href="mailto:cdcoppercoins@gmail.com">cdcoppercoins@gmail.com</a>.
    </p>

    @if(session('success'))
        <p><strong>{{ session('success') }}</strong></p>
    @else
        @if(session('error'))
            <p><strong>{{ session('error') }}</strong></p>
        @endif

        <form method="post" action="{{ route('contribute.store') }}">
            @csrf
            <p>
                <label>Your name<br>
                    <input name="name" value="{{ old('name') }}" style="width:100%; padding:10px;" required>
                </label>
            </p>
            <p>
                <label>Your email<br>
                    <input type="email" name="email" value="{{ old('email') }}" style="width:100%; padding:10px;" required>
                </label>
            </p>
            <p>
                <label>Message<br>
                    <textarea name="message" rows="10" style="width:100%; padding:10px;" required>{{ old('message') }}</textarea>
                </label>
            </p>

            <!-- honeypot (bots fill this, humans don't) -->
            <div style="position:absolute; left:-9999px;">
                <label>Company <input name="company" tabindex="-1" autocomplete="off"></label>
            </div>

            <p><button type="submit" style="padding:12px 18px;">Send</button></p>
        </form>

        <p>
            Postal address:<br>
            Minilicenseplates<br>
            PO Box 2364<br>
            Smithfield, NC 27577
        </p>
    @endif
</div>
@endsection

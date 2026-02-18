@extends('layouts.app')

@section('title', 'MiniLicensePlates.com')

@section('content')
<div class="set-width">
    <h1>MiniLicensePlates.com</h1>

    <p>
        A visual reference library of miniature license plate toys issued with candy, gum, and cereal — plus related
        bicycle vanity plates and other products.
    </p>

    <p>
        <a class="home-box" href="{{ route('gallery') }}">Enter the Gallery</a>
    </p>

    <p>
        New here? Start with <a href="{{ route('about') }}">About</a> or browse the <a href="{{ route('history') }}">History</a>.
    </p>
</div>
@endsection

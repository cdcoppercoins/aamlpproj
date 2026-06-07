@extends('layouts.app')

@section('title', 'Unsubscribed | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page auth-page">
    <h1>Unsubscribed</h1>
    <p class="auth-lead">
        {{ $user->email }} will no longer receive member email newsletters from MiniLicensePlates.com.
    </p>
    <p>
        You can turn member emails back on anytime from your
        <a href="{{ route('profile.edit') }}">profile settings</a> when signed in.
    </p>
    <p class="auth-actions">
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('home') }}">Back to home</a>
    </p>
</div>
@endsection

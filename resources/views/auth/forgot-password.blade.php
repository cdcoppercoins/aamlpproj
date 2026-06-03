@extends('layouts.app')

@section('title', 'Forgot Password | MiniLicensePlates.com')

@section('meta_description', 'Request a password reset link for your MiniLicensePlates.com member account.')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page auth-page">
    <h1>Forgot your password?</h1>
    <p class="auth-lead">
        Enter the email address on your member account. We will send a link to reset your password.
    </p>

    <form class="auth-form" method="post" action="{{ route('password.email') }}">
        @csrf
        <label class="auth-field">
            <span class="auth-label">Email</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        </label>
        <p class="auth-actions">
            <button type="submit" class="home-primary-btn">Email reset link</button>
        </p>
    </form>

    <p class="auth-footer">
        <a href="{{ route('login') }}">Back to sign in</a>
    </p>
</div>
@endsection

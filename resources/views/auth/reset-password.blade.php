@extends('layouts.app')

@section('title', 'Reset Password | MiniLicensePlates.com')

@section('meta_description', 'Choose a new password for your MiniLicensePlates.com member account.')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page auth-page">
    <h1>Choose a new password</h1>
    <p class="auth-lead">
        Enter your email and a new password for your member account.
    </p>

    <form class="auth-form" method="post" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="auth-field">
            <span class="auth-label">Email</span>
            <input type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required autofocus>
        </label>
        <label class="auth-field">
            <span class="auth-label">New password</span>
            <input type="password" name="password" autocomplete="new-password" required minlength="8">
        </label>
        <label class="auth-field">
            <span class="auth-label">Confirm new password</span>
            <input type="password" name="password_confirmation" autocomplete="new-password" required>
        </label>
        <p class="auth-actions">
            <button type="submit" class="home-primary-btn">Reset password</button>
        </p>
    </form>

    <p class="auth-footer">
        <a href="{{ route('login') }}">Back to sign in</a>
    </p>
</div>
@endsection

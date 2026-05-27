@extends('layouts.app')

@section('title', 'Create Member Account | MiniLicensePlates.com')

@section('meta_description', 'Register a free member account to track your miniature license plate collection online.')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page auth-page">
    <h1>Create your account</h1>
    <p class="auth-lead">
        Choose a username and password to start tracking plates from our catalog in your own collection.
    </p>

    <form class="auth-form" method="post" action="{{ route('register') }}">
        @csrf
        <label class="auth-field">
            <span class="auth-label">Display name</span>
            <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
        </label>
        <label class="auth-field">
            <span class="auth-label">Username</span>
            <input type="text" name="username" value="{{ old('username') }}" autocomplete="username" required pattern="[A-Za-z0-9_-]+" minlength="3" maxlength="30">
            <span class="auth-hint">Letters, numbers, dashes, and underscores only.</span>
        </label>
        <label class="auth-field">
            <span class="auth-label">Email</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
        </label>
        <label class="auth-field">
            <span class="auth-label">Password</span>
            <input type="password" name="password" autocomplete="new-password" required minlength="8">
        </label>
        <label class="auth-field">
            <span class="auth-label">Confirm password</span>
            <input type="password" name="password_confirmation" autocomplete="new-password" required>
        </label>
        <p class="auth-actions">
            <button type="submit" class="home-primary-btn">Create account</button>
        </p>
    </form>

    <p class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </p>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Member Sign In | MiniLicensePlates.com')

@section('meta_description', 'Sign in to track your miniature license plate collection — quantity, condition, and notes for each catalog listing.')

@section('robots', 'noindex, follow')

@section('content')
<div class="auth-page set-width">
    <h1>Member sign in</h1>
    <p class="auth-lead">
        Sign in to manage your personal collection — record what you own, condition, quantity, and notes.
    </p>

    <form class="auth-form" method="post" action="{{ route('login') }}">
        @csrf
        <label class="auth-field">
            <span class="auth-label">Username or email</span>
            <input type="text" name="login" value="{{ old('login') }}" autocomplete="username" required autofocus>
        </label>
        <label class="auth-field">
            <span class="auth-label">Password</span>
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <label class="auth-checkbox">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
            Keep me signed in
        </label>
        <p class="auth-actions">
            <button type="submit" class="home-primary-btn">Sign in</button>
        </p>
    </form>

    <p class="auth-footer">
        New member? <a href="{{ route('register') }}">Create an account</a>
    </p>
</div>
@endsection

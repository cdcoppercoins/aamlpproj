@extends('layouts.app')

@section('title', 'Edit Profile | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="auth-page set-width profile-page">
    <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
        <ol class="gallery-breadcrumbs-list">
            <li><a href="{{ route('collection.index') }}">My Collection</a></li>
            <li aria-current="page">Profile</li>
        </ol>
    </nav>

    <h1>Your profile</h1>
    <p class="auth-lead">
        Update your contact details and profile photo. Username cannot be changed here.
    </p>

    <form class="auth-form profile-form" method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="profile-avatar-section">
            @if ($user->profileImageUrl())
                <img src="{{ $user->profileImageUrl() }}"
                     alt="Profile photo for {{ $user->name }}"
                     class="profile-avatar-preview">
            @else
                <div class="profile-avatar-placeholder" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            @endif
            <div class="profile-avatar-fields">
                <label class="auth-field">
                    <span class="auth-label">Profile photo</span>
                    <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp,image/gif">
                    <span class="auth-hint">Optional. JPG, PNG, WebP, or GIF — max 2 MB.</span>
                </label>
                @if ($user->profile_image)
                    <label class="auth-checkbox">
                        <input type="checkbox" name="remove_profile_image" value="1">
                        Remove current photo
                    </label>
                @endif
            </div>
        </div>

        <label class="auth-field">
            <span class="auth-label">Display name</span>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
        </label>

        <label class="auth-field">
            <span class="auth-label">Username</span>
            <input type="text" value="{{ $user->username }}" disabled aria-readonly="true">
            <span class="auth-hint">Username is fixed for sign-in.</span>
        </label>

        <label class="auth-field">
            <span class="auth-label">Email</span>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
        </label>

        <label class="auth-field">
            <span class="auth-label">Phone</span>
            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" maxlength="30" placeholder="Optional">
        </label>

        <label class="auth-field">
            <span class="auth-label">Mailing address</span>
            <textarea name="address" rows="4" maxlength="1000" placeholder="Optional — street, city, state, ZIP">{{ old('address', $user->address) }}</textarea>
        </label>

        <p class="auth-actions">
            <button type="submit" class="home-primary-btn">Save profile</button>
            <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.index') }}">Cancel</a>
        </p>
    </form>
</div>
@endsection

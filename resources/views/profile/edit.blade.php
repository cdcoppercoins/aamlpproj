@extends('layouts.app')

@section('title', 'Edit Profile | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page auth-page profile-page">
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
            <div class="profile-avatar-preview-slot">
                <img id="profile-avatar-preview-img"
                     src="{{ $user->profileImageUrl() ?? '' }}"
                     alt="Profile photo preview for {{ $user->name }}"
                     class="profile-avatar-preview"
                     data-initial-src="{{ $user->profileImageUrl() ?? '' }}"
                     @if (! $user->profileImageUrl()) hidden @endif>
                <div id="profile-avatar-preview-placeholder"
                     class="profile-avatar-placeholder"
                     @if ($user->profileImageUrl()) hidden @endif
                     aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            </div>
            <div class="profile-avatar-fields">
                @include('components.profile-photo-field', ['user' => $user])
                @if ($user->profile_image)
                    <label class="auth-checkbox profile-remove-photo">
                        <input type="checkbox" name="remove_profile_image" value="1">
                        Delete my profile photo when I save (show my initial instead of a picture)
                    </label>
                    <span class="auth-hint profile-remove-photo-hint">Leave unchecked if you only want to replace the photo — use Choose photo… above, then Save profile.</span>
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

@push('scripts')
<script>
(function () {
    var input = document.getElementById('profile-image-file');
    var previewImg = document.getElementById('profile-avatar-preview-img');
    var placeholder = document.getElementById('profile-avatar-preview-placeholder');
    var removeCheckbox = document.querySelector('input[name="remove_profile_image"]');

    if (!input || !previewImg) {
        return;
    }

    var initialSrc = previewImg.getAttribute('data-initial-src') || '';

    function showPlaceholder() {
        previewImg.hidden = true;
        previewImg.removeAttribute('src');
        if (placeholder) {
            placeholder.hidden = false;
        }
    }

    function showImageSrc(src) {
        if (!src) {
            showPlaceholder();
            return;
        }
        previewImg.src = src;
        previewImg.hidden = false;
        if (placeholder) {
            placeholder.hidden = true;
        }
    }

    input.addEventListener('change', function () {
        if (removeCheckbox) {
            removeCheckbox.checked = false;
        }

        var file = this.files && this.files[0];
        if (!file) {
            showImageSrc(initialSrc);
            return;
        }

        if (!file.type || file.type.indexOf('image/') !== 0) {
            return;
        }

        var reader = new FileReader();
        reader.onload = function (event) {
            showImageSrc(event.target && event.target.result ? event.target.result : initialSrc);
        };
        reader.readAsDataURL(file);
    });

    if (removeCheckbox) {
        removeCheckbox.addEventListener('change', function () {
            if (this.checked) {
                input.value = '';
                showPlaceholder();
            } else {
                showImageSrc(initialSrc);
            }
        });
    }
})();
</script>
@endpush

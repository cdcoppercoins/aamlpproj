@php
    $inputId = 'profile-image-file';
    $user = $user ?? auth()->user();
@endphp

<div class="auth-field profile-photo-field admin-file-picker">
    <span class="auth-label">Profile photo</span>
    <label class="admin-file-picker-control" for="{{ $inputId }}">
        <span class="home-secondary-btn admin-file-picker-btn">Choose photo…</span>
        <input type="file"
               name="profile_image"
               id="{{ $inputId }}"
               class="admin-file-picker-input"
               accept="image/jpeg,image/png,image/webp,image/gif">
    </label>
    <p class="admin-file-picker-name" id="{{ $inputId }}-name" hidden>
        Selected: <span></span>
    </p>
    @error('profile_image')
        <p class="auth-error">{{ $message }}</p>
    @enderror
    <span class="auth-hint">Optional. JPG, PNG, WebP, or GIF — max 2 MB. The preview on the left updates when you choose a file; click <strong>Save profile</strong> to keep it.</span>
</div>

@push('scripts')
<script>
document.getElementById(@json($inputId))?.addEventListener('change', function () {
    var nameBox = document.getElementById(@json($inputId . '-name'));
    if (!nameBox) {
        return;
    }
    var fileName = this.files && this.files[0] ? this.files[0].name : '';
    var nameSpan = nameBox.querySelector('span');
    if (fileName && nameSpan) {
        nameSpan.textContent = fileName;
        nameBox.hidden = false;
    } else {
        nameBox.hidden = true;
    }
});
</script>
@endpush

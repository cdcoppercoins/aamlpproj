@php
    $inputId = $inputId ?? 'hero-image-file';
@endphp

<div class="admin-form-grid">
    <label class="auth-field">
        <span class="auth-label">Display order</span>
        <input type="number" name="sort_order" value="{{ old('sort_order', $slide->sort_order) }}" min="1" max="999" required>
        <span class="auth-hint">Lower numbers appear first.</span>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Slide options</span>
        <span class="admin-checkbox-label">
            <input type="checkbox" name="fill_slide" value="1" @checked(old('fill_slide', $slide->fill_slide))>
            Fill entire slide area (crop image to fit)
        </span>
        <span class="admin-checkbox-label">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $slide->is_active))>
            Show this slide on the home page
        </span>
        <span class="auth-hint">For full-slide images, use wide photos about 1150×320 px. Headline text still appears in the bar at the bottom.</span>
    </label>

    <div class="auth-field admin-form-grid-span-3 admin-file-picker">
        <span class="auth-label">
            Banner image
            @if (! ($requireImage ?? false))
                <span class="auth-hint auth-hint-inline">(optional — leave blank to keep the current image)</span>
            @endif
        </span>
        @if (! empty($slide->image))
            <div class="admin-hero-preview">
                <img src="{{ asset($slide->image) }}" alt="" width="460" height="112">
                <p class="auth-hint">Current file: <code>{{ $slide->image }}</code></p>
            </div>
        @endif
        <label class="admin-file-picker-control" for="{{ $inputId }}">
            <span class="home-secondary-btn admin-file-picker-btn">Choose image file…</span>
            <input type="file"
                   name="image_file"
                   id="{{ $inputId }}"
                   class="admin-file-picker-input"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   @if($requireImage ?? false) required @endif>
        </label>
        <p class="admin-file-picker-name" id="{{ $inputId }}-name" @if(! old('image_file_name')) hidden @endif>
            Selected: <span>{{ old('image_file_name') }}</span>
        </p>
        @error('image_file')
            <p class="auth-error">{{ $message }}</p>
        @enderror
        <span class="auth-hint">Recommended about 1150×280 px. JPG, PNG, GIF, or WebP — max 5 MB. Text is added by the site.</span>
    </div>

    <label class="auth-field admin-form-grid-span-3">
        <span class="auth-label">Image description (alt text)</span>
        <input type="text" name="alt" value="{{ old('alt', $slide->alt) }}" maxlength="255" required>
    </label>
    <label class="auth-field admin-form-grid-span-3">
        <span class="auth-label">Headline</span>
        <input type="text" name="headline" value="{{ old('headline', $slide->headline) }}" maxlength="255" required>
    </label>
    <label class="auth-field admin-form-grid-span-3">
        <span class="auth-label">Subline</span>
        <input type="text" name="subline" value="{{ old('subline', $slide->subline) }}" maxlength="500">
    </label>
    <label class="auth-field">
        <span class="auth-label">Button text</span>
        <input type="text" name="cta" value="{{ old('cta', $slide->cta) }}" maxlength="128" placeholder="Browse the Gallery">
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Button link</span>
        <select name="link_key" id="hero-link-key">
            @foreach ($linkOptionGroups as $groupLabel => $options)
                <optgroup label="{{ $groupLabel }}">
                    @foreach ($options as $option)
                        <option value="{{ $option['key'] }}" @selected(old('link_key', \App\Support\HeroLinkOptions::keyFromSlide($slide)) === $option['key'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <span class="auth-hint">Pick from the list, or choose <strong>Custom URL…</strong> and paste a copied address-bar link.</span>
    </label>
    <label class="auth-field admin-form-grid-span-3"
           id="hero-custom-link-field"
           @if(old('link_key', \App\Support\HeroLinkOptions::keyFromSlide($slide)) !== '_custom') hidden @endif>
        <span class="auth-label">Custom URL</span>
        <input type="text"
               name="custom_link_url"
               id="hero-custom-link-input"
               value="{{ old('custom_link_url', \App\Support\HeroLinkOptions::customUrlFromSlide($slide)) }}"
               maxlength="500"
               placeholder="/search?search=1&amp;jurisdiction=Indiana">
        <span class="auth-hint">Paste a path like <code>/search?search=1&amp;jurisdiction=Indiana</code>. Empty fields (<code>year=</code>) are ignored.</span>
    </label>
    <label class="auth-field admin-form-grid-span-3">
        <span class="auth-label">Background gradient (CSS)</span>
        <input type="text" name="bg" value="{{ old('bg', $slide->bg) }}" maxlength="512" required>
        <span class="auth-hint">Example: linear-gradient(135deg, #2d6388 0%, #4079a5 55%, #5a96bc 100%)</span>
    </label>
</div>

<script>
(function () {
    var select = document.getElementById('hero-link-key');
    var customField = document.getElementById('hero-custom-link-field');
    if (!select || !customField) {
        return;
    }

    function syncCustomField() {
        customField.hidden = select.value !== '_custom';
    }

    select.addEventListener('change', syncCustomField);
    syncCustomField();
})();
</script>

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

@endpush

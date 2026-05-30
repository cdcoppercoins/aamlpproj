@php
    /** @var \App\Models\SiteLink $link */
    $destinationType = old('destination_type', $link->page_id ? 'page' : 'url');
@endphp

<div class="admin-form-grid">
    <div class="admin-publish-panel admin-form-grid-span-full">
        <label class="auth-checkbox admin-publish-checkbox">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $link->is_published ?? true))>
            <strong>Show this link on the live site</strong>
        </label>
        <p class="auth-hint">Page links also require the static page to be published. Custom URLs go live when this box is checked.</p>
    </div>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Link text</span>
        <input type="text" name="label" value="{{ old('label', $link->label) }}" maxlength="120" required>
        <span class="auth-hint">What visitors see in the menu or footer.</span>
    </label>
    <label class="auth-field">
        <span class="auth-label">List order</span>
        <input type="number" name="sort_order" value="{{ old('sort_order', $link->sort_order) }}" min="0" max="9999" required>
    </label>
    <fieldset class="auth-field admin-form-grid-span-full admin-fieldset">
        <legend class="auth-label">Destination</legend>
        <label class="auth-radio">
            <input type="radio" name="destination_type" value="page" @checked($destinationType === 'page')>
            Static page
        </label>
        <label class="auth-radio">
            <input type="radio" name="destination_type" value="url" @checked($destinationType === 'url')>
            Custom URL
        </label>
    </fieldset>
    <label class="auth-field admin-form-grid-span-2" data-destination-field="page">
        <span class="auth-label">Static page</span>
        <select name="page_id">
            <option value="">Choose a page…</option>
            @foreach ($pages as $page)
                <option value="{{ $page->id }}" @selected((string) old('page_id', $link->page_id) === (string) $page->id)>
                    {{ $page->title }} (/{{ $page->slug }})@if (! $page->is_published) — draft @endif
                </option>
            @endforeach
        </select>
    </label>
    <label class="auth-field admin-form-grid-span-2" data-destination-field="url">
        <span class="auth-label">Custom URL</span>
        <input type="text"
               name="url"
               value="{{ old('url', $link->url) }}"
               maxlength="500"
               placeholder="/contribute or https://example.com/page">
        <span class="auth-hint">Use a site path like <code>/contribute</code> or a full <code>https://</code> link for external sites.</span>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Show in</span>
        <select name="placement" required>
            @foreach ($placements as $value => $label)
                <option value="{{ $value }}" @selected(old('placement', $link->placement) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.admin-site-link-form');
    if (!form) {
        return;
    }

    var radios = form.querySelectorAll('input[name="destination_type"]');
    var pageField = form.querySelector('[data-destination-field="page"]');
    var urlField = form.querySelector('[data-destination-field="url"]');

    function syncDestinationFields() {
        var selected = form.querySelector('input[name="destination_type"]:checked');
        var type = selected ? selected.value : 'page';
        if (pageField) {
            pageField.hidden = type !== 'page';
        }
        if (urlField) {
            urlField.hidden = type !== 'url';
        }
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', syncDestinationFields);
    });

    syncDestinationFields();
});
</script>
@endpush

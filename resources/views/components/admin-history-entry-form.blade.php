@php
    /** @var \App\Models\HistoryTimelineEntry $entry */
    $imageUrl = $entry->imagePath() ? asset($entry->imagePath()) : null;
@endphp

<div class="admin-form-grid">
    <label class="auth-field">
        <span class="auth-label">Display order</span>
        <input type="number" name="sort_order" value="{{ old('sort_order', $entry->sort_order) }}" min="1" max="9999" required>
    </label>
    <label class="auth-field">
        <span class="auth-label">URL id (slug)</span>
        <input type="text" name="slug" value="{{ old('slug', $entry->slug) }}" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="80" required>
        <span class="auth-hint">Lowercase letters, numbers, and hyphens only (e.g. goudey-cards).</span>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Timeline label</span>
        <input type="text" name="label" value="{{ old('label', $entry->label) }}" maxlength="255" required>
        <span class="auth-hint">Short text shown on the timeline row (often a year or product name).</span>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Story headline</span>
        <input type="text" name="title" value="{{ old('title', $entry->title) }}" maxlength="255" required>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Story text (HTML allowed)</span>
        <textarea name="body" rows="14" required>{{ old('body', $entry->body) }}</textarea>
        <span class="auth-hint">Use &lt;p&gt;...&lt;/p&gt; for paragraphs.</span>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Image description (accessibility)</span>
        <input type="text" name="alt" value="{{ old('alt', $entry->alt) }}" maxlength="255">
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Image caption (optional)</span>
        <input type="text" name="caption" value="{{ old('caption', $entry->caption) }}" maxlength="2000">
        <span class="auth-hint">Shown in italics under the photo when expanded.</span>
    </label>
    <div class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Timeline image</span>
        @if ($imageUrl)
            <p class="admin-history-image-preview">
                <img src="{{ $imageUrl }}" alt="" width="120" height="120" class="admin-history-thumb-preview">
            </p>
            <label class="auth-checkbox">
                <input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))>
                Remove current image
            </label>
        @endif
        <input type="file" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp">
        <span class="auth-hint">Saved to public/history-media/. Recommended: landscape JPG, about 800×400.</span>
    </div>
    <label class="auth-checkbox admin-form-grid-span-2">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $entry->is_published ?? true))>
        Published (visible on the History page)
    </label>
</div>

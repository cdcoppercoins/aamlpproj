@php
    /** @var \App\Models\Page $page */
@endphp

<div class="admin-form-grid">
    <div class="admin-publish-panel admin-form-grid-span-2">
        <label class="auth-checkbox admin-publish-checkbox">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published ?? false))>
            <strong>Publish on the live site</strong>
        </label>
        <p class="auth-hint">Uncheck to keep as draft. Published pages appear at <code>/your-slug</code> (e.g. <code>/about</code>).</p>
    </div>
    <label class="auth-field">
        <span class="auth-label">List order</span>
        <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}" min="0" max="9999" required>
    </label>
    <label class="auth-field">
        <span class="auth-label">URL slug</span>
        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="120" required>
        <span class="auth-hint">Lowercase letters, numbers, hyphens. Becomes <strong>/slug</strong> on the site.</span>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Page title</span>
        <input type="text" name="title" value="{{ old('title', $page->title) }}" maxlength="255" required>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Meta description (optional)</span>
        <input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" maxlength="500">
        <span class="auth-hint">Short summary for search engines. Leave blank to use an excerpt from the body.</span>
    </label>
    <div class="auth-field admin-form-grid-span-full admin-page-editor-field">
        <span class="auth-label">Page content</span>
        <p class="auth-hint">Drag images into the editor, drag corners to resize, and use <strong>left/right align</strong> on an image so text wraps around it.</p>
        <textarea id="page-body-editor" name="body" class="admin-page-body-field" rows="20" required>{{ old('body', $page->body) }}</textarea>
    </div>
</div>

@push('scripts')
@include('components.admin-page-editor-script')
@endpush

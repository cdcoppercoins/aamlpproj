@php
    /** @var \App\Models\Article $article */
    $heroUrl = $article->heroImagePath() ? asset($article->heroImagePath()) : null;
    $publishedValue = old('published_at', $article->published_at?->format('Y-m-d'));
@endphp

<div class="admin-form-grid">
    <div class="admin-publish-panel admin-form-grid-span-2">
        <label class="auth-checkbox admin-publish-checkbox">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $article->is_published ?? false))>
            <strong>Publish on the public Articles page</strong>
        </label>
        <p class="auth-hint">Uncheck to keep as draft. Check and save to go live.</p>
        <label class="auth-field admin-publish-date">
            <span class="auth-label">Publish date (optional)</span>
            <input type="date" name="published_at" value="{{ $publishedValue }}">
            <span class="auth-hint">Leave blank to use today when you publish.</span>
        </label>
    </div>
    <label class="auth-field">
        <span class="auth-label">List order</span>
        <input type="number" name="sort_order" value="{{ old('sort_order', $article->sort_order) }}" min="0" max="9999" required>
        <span class="auth-hint">Higher sorts first when dates match.</span>
    </label>
    <label class="auth-field">
        <span class="auth-label">URL id (slug)</span>
        <input type="text" name="slug" value="{{ old('slug', $article->slug) }}" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="120" required>
        <span class="auth-hint">Lowercase letters, numbers, hyphens (e.g. wheaties-mail-away).</span>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Author</span>
        <input type="text" name="author" value="{{ old('author', $article->author) }}" maxlength="255" required>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Title</span>
        <input type="text" name="title" value="{{ old('title', $article->title) }}" maxlength="255" required>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Subtitle</span>
        <input type="text" name="subtitle" value="{{ old('subtitle', $article->subtitle) }}" maxlength="500">
        <span class="auth-hint">Short line under the title on the public page.</span>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Body (HTML allowed)</span>
        <textarea name="body" rows="16" required>{{ old('body', $article->body) }}</textarea>
        <span class="auth-hint">Use &lt;p&gt;...&lt;/p&gt; for paragraphs.</span>
    </label>
    <div class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Featured image</span>
        @if ($heroUrl)
            <p class="admin-history-image-preview">
                <img src="{{ $heroUrl }}" alt="" width="200" class="admin-article-hero-preview">
            </p>
            <label class="auth-checkbox">
                <input type="checkbox" name="remove_hero_image" value="1" @checked(old('remove_hero_image'))>
                Remove featured image
            </label>
        @endif
        <input type="file" name="hero_image_file" accept="image/jpeg,image/png,image/gif,image/webp">
        <span class="auth-hint">Saved to public/articles-media/.</span>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Featured image description (accessibility)</span>
        <input type="text" name="hero_image_alt" value="{{ old('hero_image_alt', $article->hero_image_alt) }}" maxlength="255">
    </label>
    @if ($article->exists && $article->images->isNotEmpty())
        <div class="auth-field admin-form-grid-span-2">
            <span class="auth-label">Current additional images</span>
            <ul class="admin-article-image-list">
                @foreach ($article->images as $image)
                    <li class="admin-article-image-list-item">
                        <img src="{{ $image->imageUrl() }}" alt="" width="80" height="80" class="admin-history-thumb">
                        <label class="auth-checkbox">
                            <input type="checkbox" name="delete_image_ids[]" value="{{ $image->id }}">
                            Remove
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Add images</span>
        <input type="file" name="image_files[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
        <span class="auth-hint">Optional gallery images shown below the body. You can select several at once.</span>
    </div>
</div>

@php
    /** @var \App\Models\MemberNewsletterCampaign $campaign */
@endphp

<div class="admin-form-grid">
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Email subject</span>
        <input type="text" name="subject" value="{{ old('subject', $campaign->subject) }}" maxlength="255" required>
    </label>
    <label class="auth-field admin-form-grid-span-2">
        <span class="auth-label">Preview line (optional)</span>
        <input type="text" name="preview_text" value="{{ old('preview_text', $campaign->preview_text) }}" maxlength="255">
        <span class="auth-hint">Short summary some mail apps show beside the subject.</span>
    </label>
    <div class="auth-field admin-form-grid-span-full">
        <span class="auth-label">Newsletter body (HTML)</span>
        <p class="auth-hint">Use simple HTML: paragraphs, links, bold, lists. Scripts and iframes are removed automatically.</p>
        <textarea name="body_html" class="admin-newsletter-body-field" rows="18" required>{{ old('body_html', $campaign->body_html) }}</textarea>
    </div>
</div>

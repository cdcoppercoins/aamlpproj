@php
    $defaultGrade = 'MT';
@endphp
<section class="collection-quick-fill" aria-label="Quick fill defaults">
    <h3 class="collection-quick-fill-title">Quick fill defaults</h3>
    <p class="collection-quick-fill-lead">
        Own a complete run? Set how many items to add per plate, a default grade, storage, and notes, then fill every row at once.
    </p>

    <form id="collection-quick-fill-form" method="post" action="{{ route('collection.manage.fill') }}" class="collection-quick-fill-form">
        @csrf

        <div class="collection-quick-fill-grid">
            @if (! $selectedSet)
                <label class="collection-quick-fill-field collection-quick-fill-field-set">
                    <span class="auth-label">Set</span>
                    <select name="set_name" class="collection-manage-set-select" required>
                        <option value="">— Select a set —</option>
                        @foreach ($setNames as $set)
                            <option value="{{ $set->set_name }}">
                                {{ $set->set_name }}
                                @if ($set->year) ({{ $set->year }}) @endif
                            </option>
                        @endforeach
                    </select>
                </label>
            @else
                <input type="hidden" name="set_name" value="{{ $selectedSet }}">
            @endif

            <label class="collection-quick-fill-field">
                <span class="auth-label">Items per plate</span>
                <input type="number"
                       id="collection-default-item-count"
                       name="item_count"
                       value="1"
                       min="1"
                       max="9999"
                       class="collection-manage-input"
                       required>
            </label>

            <label class="collection-quick-fill-field">
                <span class="auth-label">Default grade</span>
                <select id="collection-default-grade"
                        name="grade"
                        class="collection-manage-input collection-manage-grade">
                    <option value="">— None —</option>
                    @foreach ($grades as $code => $label)
                        <option value="{{ $code }}" @selected($code === $defaultGrade)>{{ $code }} — {{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="collection-quick-fill-field">
                <span class="auth-label">Apply to</span>
                <select name="mode" class="collection-visibility-select" id="collection-fill-mode">
                    <option value="empty" @selected($selectedSet)>Empty rows only</option>
                    <option value="all" @selected(! $selectedSet)>Entire set (overwrite existing)</option>
                </select>
            </label>

            <label class="collection-quick-fill-field">
                <span class="auth-label">Storage</span>
                <input type="text"
                       id="collection-default-storage"
                       name="storage_location"
                       value=""
                       maxlength="128"
                       class="collection-manage-input"
                       placeholder="Binder, box…">
            </label>

            <label class="collection-quick-fill-field collection-quick-fill-field--wide">
                <span class="auth-label">Notes</span>
                <input type="text"
                       id="collection-default-notes"
                       name="notes"
                       value=""
                       maxlength="5000"
                       class="collection-manage-input"
                       placeholder="Private notes for each plate">
            </label>
        </div>

        <div class="collection-quick-fill-actions">
            @if ($selectedSet)
                <button type="button" class="home-primary-btn home-primary-btn-secondary" id="collection-apply-to-form">
                    Apply to form (preview)
                </button>
            @endif
            <button type="submit" class="home-primary-btn">Fill set &amp; save</button>
        </div>
    </form>
</section>

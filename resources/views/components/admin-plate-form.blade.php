@php
    $isEdit = $plate->exists;
    $formAction = $isEdit
        ? route('admin.catalog.plates.update', $plate)
        : route('admin.catalog.plates.store', $setMeta->set_code);
@endphp

<form class="admin-form admin-catalog-form" method="post" action="{{ $formAction }}" enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <fieldset class="admin-fieldset">
        <legend>Set context</legend>
        <dl class="admin-detail-list admin-detail-list-compact">
            <div>
                <dt>Set code</dt>
                <dd><code>{{ $setMeta->set_code }}</code></dd>
            </div>
            <div>
                <dt>Set name</dt>
                <dd>{{ $setMeta->set_name }}</dd>
            </div>
            @if ($setMeta->company)
                <div>
                    <dt>Company</dt>
                    <dd>{{ $setMeta->company }}</dd>
                </div>
            @endif
            @if ($setMeta->year)
                <div>
                    <dt>Year</dt>
                    <dd>{{ $setMeta->year }}</dd>
                </div>
            @endif
        </dl>
        <p class="admin-note">
            <a href="{{ route('admin.catalog.sets.edit', $setMeta->set_code) }}" class="admin-inline-link">Edit set details</a>
            for all plates in this set.
        </p>
    </fieldset>

    <fieldset class="admin-fieldset">
        <legend>Identification</legend>
        <div class="admin-form-grid">
            <label class="auth-field">
                <span class="auth-label">Catalog ref</span>
                <input type="text" name="cat_ref" value="{{ old('cat_ref', $plate->cat_ref) }}" maxlength="10">
            </label>
            <label class="auth-field">
                <span class="auth-label">Jurisdiction</span>
                <input type="text" name="jurisdiction" value="{{ old('jurisdiction', $plate->jurisdiction) }}" maxlength="128">
            </label>
            <label class="auth-field">
                <span class="auth-label">Jurisdiction type</span>
                <select name="jurisdiction_type">
                    <option value="">— None —</option>
                    @foreach ($jurisdictionTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('jurisdiction_type', $plate->jurisdiction_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="auth-field">
                <span class="auth-label">Year</span>
                <input type="number" name="year" value="{{ old('year', $plate->year) }}" min="1800" max="2100">
            </label>
            <label class="auth-field">
                <span class="auth-label">Serial number</span>
                <input type="text" name="serial_number" value="{{ old('serial_number', $plate->serial_number) }}" maxlength="64">
            </label>
            <label class="auth-field">
                <span class="auth-label">Sort order</span>
                <input type="number" name="sort_order" value="{{ old('sort_order', $plate->sort_order ?? 0) }}" min="0" max="99999">
            </label>
        </div>
    </fieldset>

    <fieldset class="admin-fieldset">
        <legend>Variety</legend>
        <div class="admin-form-grid">
            <label class="auth-field">
                <span class="auth-label">Variety key</span>
                <input type="text" name="variety_key" value="{{ old('variety_key', $plate->variety_key ?: 'base') }}" maxlength="32">
                <span class="auth-hint">Use <code>base</code> for the standard plate.</span>
            </label>
            <label class="auth-field admin-form-grid-span-2">
                <span class="auth-label">Variety notes</span>
                <textarea name="variety_notes" rows="2">{{ old('variety_notes', $plate->variety_notes) }}</textarea>
            </label>
        </div>
    </fieldset>

    <fieldset class="admin-fieldset">
        <legend>Catalog values</legend>
        <div class="admin-form-grid admin-form-grid-values">
            @foreach (['MT' => 'value_mt', 'EX' => 'value_ex', 'VG' => 'value_vg', 'G' => 'value_g', 'FR' => 'value_fr', 'PO' => 'value_po'] as $grade => $field)
                <label class="auth-field">
                    <span class="auth-label">{{ $grade }}</span>
                    <input type="text" name="{{ $field }}" value="{{ old($field, $plate->{$field}) }}" maxlength="32">
                </label>
            @endforeach
        </div>
    </fieldset>

    <fieldset class="admin-fieldset">
        <legend>Physical details</legend>
        <div class="admin-form-grid">
            <label class="auth-field">
                <span class="auth-label">Width (inches)</span>
                <input type="number" name="width_inches" value="{{ old('width_inches', $plate->width_inches) }}" step="0.0001" min="0">
            </label>
            <label class="auth-field">
                <span class="auth-label">Height (inches)</span>
                <input type="number" name="height_inches" value="{{ old('height_inches', $plate->height_inches) }}" step="0.0001" min="0">
            </label>
            <label class="auth-field">
                <span class="auth-label">State embossed</span>
                <select name="state_embossed">
                    <option value="" @selected(old('state_embossed', $plate->state_embossed) === null || old('state_embossed', '') === '')>Unknown</option>
                    <option value="1" @selected((string) old('state_embossed', $plate->state_embossed) === '1')>Yes</option>
                    <option value="0" @selected((string) old('state_embossed', $plate->state_embossed) === '0')>No</option>
                </select>
            </label>
            <label class="auth-field">
                <span class="auth-label">Legend embossed</span>
                <select name="legend_embossed">
                    <option value="" @selected(old('legend_embossed', $plate->legend_embossed) === null || old('legend_embossed', '') === '')>Unknown</option>
                    <option value="1" @selected((string) old('legend_embossed', $plate->legend_embossed) === '1')>Yes</option>
                    <option value="0" @selected((string) old('legend_embossed', $plate->legend_embossed) === '0')>No</option>
                </select>
            </label>
        </div>
    </fieldset>

    <fieldset class="admin-fieldset">
        <legend>Images</legend>
        @if ($isEdit && $plate->frontImageUrl())
            <div class="admin-plate-preview-row">
                <figure class="admin-plate-preview">
                    <img src="{{ $plate->frontImageUrl() }}" alt="Front image preview">
                    <figcaption>Front</figcaption>
                </figure>
                @if ($plate->backImageUrl())
                    <figure class="admin-plate-preview">
                        <img src="{{ $plate->backImageUrl() }}" alt="Back image preview">
                        <figcaption>Back</figcaption>
                    </figure>
                @endif
            </div>
            <p class="admin-note">Current files: <code>{{ $plate->image_base }}_a/b.{{ $plate->image_ext }}</code></p>
        @endif
        <div class="admin-form-grid">
            <label class="auth-field">
                <span class="auth-label">Image base name</span>
                <input type="text" name="image_base" value="{{ old('image_base', $plate->image_base) }}" maxlength="128" placeholder="e.g. AL or nophoto-1">
                <span class="auth-hint">Saved as <code>{base}_a.ext</code> in <code>public/plates/{{ $setMeta->set_code }}/</code></span>
            </label>
            <label class="auth-field">
                <span class="auth-label">Front image upload</span>
                <input type="file" name="front_image" accept="image/jpeg,image/png,image/webp,image/gif">
            </label>
            <label class="auth-field">
                <span class="auth-label">Back image upload</span>
                <input type="file" name="back_image" accept="image/jpeg,image/png,image/webp,image/gif">
            </label>
            @if ($isEdit)
                <label class="admin-toggle">
                    <input type="checkbox" name="remove_front_image" value="1" @checked(old('remove_front_image'))>
                    <span>Remove front image file</span>
                </label>
                <label class="admin-toggle">
                    <input type="checkbox" name="remove_back_image" value="1" @checked(old('remove_back_image'))>
                    <span>Remove back image file</span>
                </label>
            @endif
        </div>
    </fieldset>

    <fieldset class="admin-fieldset">
        <legend>Notes</legend>
        <label class="auth-field">
            <span class="auth-label">Catalog notes</span>
            <textarea name="notes" rows="4">{{ old('notes', $plate->notes) }}</textarea>
        </label>
    </fieldset>

    <p class="auth-actions">
        <button type="submit" class="home-primary-btn">{{ $isEdit ? 'Save plate' : 'Add plate' }}</button>
        <a href="{{ route('admin.catalog.sets.show', $setMeta->set_code) }}" class="admin-inline-link">Cancel</a>
    </p>
</form>

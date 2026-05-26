@extends('layouts.app')

@section('title', 'Edit Collection by Set | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page collection-manage-page">
    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('collection.index') }}">My Collection</a></li>
                <li aria-current="page">Edit by set</li>
            </ol>
        </nav>
        <h1 class="home-title">Edit collection by set</h1>
        <p class="home-lead">
            Choose a catalog set, then fill in quantity and condition for every plate you own or want.
            Leave quantity at 0 and want list unchecked to remove a row from your collection.
        </p>
    </section>

    <section class="collection-manage-picker" aria-label="Choose a set">
        <form class="collection-manage-set-form" method="get" action="{{ route('collection.manage') }}">
            <label class="collection-manage-set-label">
                <span class="auth-label">Catalog set</span>
                <select name="set_name" class="collection-manage-set-select" required onchange="this.form.submit()">
                    <option value="">— Select a set —</option>
                    @foreach ($setNames as $set)
                        <option value="{{ $set->set_name }}" @selected($selectedSet === $set->set_name)>
                            {{ $set->set_name }}
                            @if ($set->year) ({{ $set->year }}) @endif
                            @if ($set->company) — {{ $set->company }} @endif
                            — {{ number_format($set->plate_count) }} plates
                        </option>
                    @endforeach
                </select>
            </label>
            <noscript>
                <button type="submit" class="home-primary-btn">Load set</button>
            </noscript>
        </form>
    </section>

    @include('components.collection-quick-fill', [
        'setNames' => $setNames,
        'selectedSet' => null,
        'conditions' => $conditions,
    ])

    @if ($selectedSet && $plates)
        <section class="collection-manage-set-header">
            <h2 class="collection-manage-set-title">{{ $setMeta->set_name }}</h2>
            <p class="collection-manage-set-meta">
                @if ($setMeta->company){{ $setMeta->company }} · @endif
                @if ($setMeta->year){{ $setMeta->year }} · @endif
                Set code {{ $setMeta->set_code }} · {{ number_format($setMeta->plate_count) }} catalog entries
            </p>
            <p class="collection-manage-pdf-note">
                Save your changes before downloading a PDF. Catalog values are from the pricing guide at your chosen grade — shown only to you, not on public member views.
            </p>
            @if ($setCatalogTotal !== null)
                <p class="collection-set-catalog-total">
                    Set catalog value (private): <strong id="collection-set-total">{{ \App\Models\Plate::formatCatalogTotal($setCatalogTotal) }}</strong>
                </p>
            @else
                <p class="collection-set-catalog-total">
                    Set catalog value (private): <strong id="collection-set-total">--</strong>
                </p>
            @endif
        </section>

        @include('components.collection-quick-fill', [
            'setNames' => $setNames,
            'selectedSet' => $selectedSet,
            'conditions' => $conditions,
        ])

        <form class="collection-manage-table-form" method="post" action="{{ route('collection.manage.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="set_name" value="{{ $selectedSet }}">

            <div class="collection-manage-table-wrap">
                <table class="collection-manage-table">
                    <thead>
                        <tr>
                            <th scope="col" class="col-thumb">Photo</th>
                            <th scope="col" class="col-jurisdiction">Jurisdiction</th>
                            <th scope="col" class="col-variety">Variety</th>
                            <th scope="col" class="col-qty">Qty</th>
                            <th scope="col" class="col-condition">Condition</th>
                            <th scope="col" class="col-value">Catalog value</th>
                            <th scope="col" class="col-want">Want</th>
                            <th scope="col" class="col-location">Location</th>
                            <th scope="col" class="col-notes">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $placeholder = asset('plate_missing.png'); @endphp
                        @foreach ($plates as $plate)
                            @php
                                $entry = $collectionByPlateId[$plate->id] ?? null;
                                $frontUrl = $plate->frontImageUrl();
                            @endphp
                            <tr class="collection-manage-row @if($entry) collection-manage-row-has-entry @endif"
                                data-collection-row
                                data-display-values='@json($plate->catalogDisplayValuesByCondition())'
                                data-numeric-values='@json($plate->catalogNumericValuesByCondition())'>
                                <td class="col-thumb">
                                    <img src="{{ $frontUrl ?? $placeholder }}"
                                         alt=""
                                         class="collection-manage-thumb"
                                         onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                </td>
                                <td class="col-jurisdiction">
                                    <span class="collection-manage-jurisdiction">{{ $plate->jurisdiction ? strtoupper($plate->jurisdiction) : '—' }}</span>
                                    @if ($plate->serial_number)
                                        <span class="collection-manage-serial">#{{ $plate->serial_number }}</span>
                                    @endif
                                </td>
                                <td class="col-variety">
                                    {{ $plate->variety_notes ?: '—' }}
                                </td>
                                <td class="col-qty">
                                    <input type="number"
                                           name="items[{{ $plate->id }}][quantity]"
                                           value="{{ old('items.'.$plate->id.'.quantity', $entry?->quantity ?? '') }}"
                                           min="0"
                                           max="9999"
                                           class="collection-manage-input collection-manage-qty"
                                           placeholder="0"
                                           aria-label="Quantity for {{ $plate->jurisdiction ?? 'plate' }}">
                                </td>
                                <td class="col-condition">
                                    <select name="items[{{ $plate->id }}][condition]"
                                            class="collection-manage-input collection-manage-condition"
                                            aria-label="Condition for {{ $plate->jurisdiction ?? 'plate' }}">
                                        <option value="">—</option>
                                        @foreach ($conditions as $code => $label)
                                            <option value="{{ $code }}" @selected(old('items.'.$plate->id.'.condition', $entry?->condition) === $code)>{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="col-value">
                                    <span class="collection-row-value">
                                        @if ($entry && ! $entry->is_wanted)
                                            @php $entry->setRelation('plate', $plate); @endphp
                                            {{ $entry->formattedOwnedLineValue() }}
                                        @else
                                            --
                                        @endif
                                    </span>
                                </td>
                                <td class="col-want">
                                    <input type="hidden" name="items[{{ $plate->id }}][is_wanted]" value="0">
                                    <input type="checkbox"
                                           name="items[{{ $plate->id }}][is_wanted]"
                                           value="1"
                                           class="collection-manage-want"
                                           @checked(old('items.'.$plate->id.'.is_wanted', $entry?->is_wanted))
                                           aria-label="Want list for {{ $plate->jurisdiction ?? 'plate' }}">
                                </td>
                                <td class="col-location">
                                    <input type="text"
                                           name="items[{{ $plate->id }}][storage_location]"
                                           value="{{ old('items.'.$plate->id.'.storage_location', $entry?->storage_location) }}"
                                           class="collection-manage-input collection-manage-location"
                                           maxlength="128"
                                           placeholder="Binder, box…"
                                           aria-label="Storage for {{ $plate->jurisdiction ?? 'plate' }}">
                                </td>
                                <td class="col-notes">
                                    <input type="text"
                                           name="items[{{ $plate->id }}][notes]"
                                           value="{{ old('items.'.$plate->id.'.notes', $entry?->notes) }}"
                                           class="collection-manage-input collection-manage-notes"
                                           maxlength="500"
                                           placeholder="Private notes"
                                           aria-label="Notes for {{ $plate->jurisdiction ?? 'plate' }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="collection-manage-actions">
                <button type="submit" class="home-primary-btn">Save entire set</button>
                <a class="home-primary-btn home-primary-btn-secondary"
                   href="{{ route('collection.manage.pdf', ['set_name' => $selectedSet, 'scope' => 'checklist']) }}">
                    Download PDF checklist
                </a>
                <a class="home-primary-btn home-primary-btn-secondary"
                   href="{{ route('collection.manage.pdf', ['set_name' => $selectedSet, 'scope' => 'mine']) }}">
                    Download my entries (PDF)
                </a>
                <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('gallery.show', $selectedSet) }}">View set in gallery</a>
                <a class="collection-manage-cancel" href="{{ route('collection.index') }}">Back to my collection</a>
            </p>
        </form>
    @elseif ($selectedSet)
        <p class="collection-empty">No plates found for this set.</p>
    @endif
</div>

@if ($selectedSet && $plates)
<script>
(function () {
    var applyBtn = document.getElementById('collection-apply-to-form');
    var modeSelect = document.getElementById('collection-fill-mode');
    var qtyInput = document.getElementById('collection-default-qty');
    var condSelect = document.getElementById('collection-default-condition');

    function formatMoney(amount) {
        return '$' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function rowLineValue(row) {
        var want = row.querySelector('.collection-manage-want');
        if (want && want.checked) return null;

        var qtyField = row.querySelector('.collection-manage-qty');
        var condField = row.querySelector('.collection-manage-condition');
        var qty = qtyField ? parseInt(qtyField.value, 10) : 0;
        if (!qtyField || qtyField.value === '' || isNaN(qty) || qty <= 0) return null;

        var cond = condField ? condField.value : '';
        if (!cond) return null;

        var numericValues = {};
        try {
            numericValues = JSON.parse(row.getAttribute('data-numeric-values') || '{}');
        } catch (e) {}

        var unit = numericValues[cond];
        if (unit === null || unit === undefined) return null;

        return unit * qty;
    }

    function rowValueLabel(row, lineValue) {
        if (lineValue === null) return '--';

        var condField = row.querySelector('.collection-manage-condition');
        var qtyField = row.querySelector('.collection-manage-qty');
        var cond = condField ? condField.value : '';
        var qty = qtyField ? parseInt(qtyField.value, 10) : 1;
        var displayValues = {};
        try {
            displayValues = JSON.parse(row.getAttribute('data-display-values') || '{}');
        } catch (e) {}

        var unitLabel = displayValues[cond] || formatMoney(lineValue / qty);
        if (qty > 1) {
            return formatMoney(lineValue) + ' (' + qty + ' × ' + unitLabel + ')';
        }
        return formatMoney(lineValue);
    }

    function recalculateSetTotal() {
        var totalEl = document.getElementById('collection-set-total');
        if (!totalEl) return;

        var total = 0;
        var hasValue = false;
        document.querySelectorAll('[data-collection-row]').forEach(function (row) {
            var valueEl = row.querySelector('.collection-row-value');
            var line = rowLineValue(row);
            if (valueEl) {
                valueEl.textContent = rowValueLabel(row, line);
            }
            if (line !== null) {
                total += line;
                hasValue = true;
            }
        });

        totalEl.textContent = hasValue ? formatMoney(total) : '--';
    }

    document.querySelectorAll('.collection-manage-qty, .collection-manage-condition, .collection-manage-want').forEach(function (el) {
        el.addEventListener('change', recalculateSetTotal);
        el.addEventListener('input', recalculateSetTotal);
    });

    function rowIsEmpty(row) {
        var qty = row.querySelector('.collection-manage-qty');
        var want = row.querySelector('.collection-manage-want');
        var qtyVal = qty ? parseInt(qty.value, 10) : 0;
        if (want && want.checked) return false;
        return !qty || qty.value === '' || isNaN(qtyVal) || qtyVal <= 0;
    }

    function applyDefaultsToForm() {
        if (!qtyInput || !condSelect || !modeSelect) return;
        var qty = qtyInput.value;
        var cond = condSelect.value;
        var fillAll = modeSelect.value === 'all';
        var rows = document.querySelectorAll('[data-collection-row]');
        var count = 0;

        rows.forEach(function (row) {
            if (!fillAll && !rowIsEmpty(row)) return;

            var qtyField = row.querySelector('.collection-manage-qty');
            var condField = row.querySelector('.collection-manage-condition');
            var wantField = row.querySelector('.collection-manage-want');

            if (qtyField) qtyField.value = qty;
            if (condField) condField.value = cond;
            if (wantField) wantField.checked = false;
            row.classList.add('collection-manage-row-has-entry');
            count++;
        });

        recalculateSetTotal();

        if (applyBtn) {
            var original = applyBtn.textContent;
            applyBtn.textContent = 'Applied to ' + count + ' rows';
            setTimeout(function () { applyBtn.textContent = original; }, 2000);
        }
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', applyDefaultsToForm);
    }
})();
</script>
@endif
@endsection

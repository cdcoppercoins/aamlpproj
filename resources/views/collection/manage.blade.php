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
            Choose a catalog set, then record each item you own or mark plates on your want list.
            Remove all items and leave want list unchecked to remove a row from your collection.
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
        'grades' => $grades,
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
            'grades' => $grades,
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
                            <th scope="col" class="col-items">Items</th>
                            <th scope="col" class="col-value">Catalog value</th>
                            <th scope="col" class="col-want">Want</th>
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
                                <td class="col-items">
                                    @include('components.collection-items-editor', [
                                        'namePrefix' => 'items['.$plate->id.'][owned_items]',
                                        'itemRows' => old('items.'.$plate->id.'.owned_items', $entry ? $entry->ownedItemsFormRows() : []),
                                        'gradeOptions' => $grades,
                                        'inputClass' => 'collection-manage-input',
                                        'compact' => true,
                                        'plateLabel' => $plate->jurisdiction ? strtoupper($plate->jurisdiction) : 'plate',
                                    ])
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
@include('components.collection-items-script')
<script>
(function () {
    var applyBtn = document.getElementById('collection-apply-to-form');
    var modeSelect = document.getElementById('collection-fill-mode');
    var countInput = document.getElementById('collection-default-item-count');
    var gradeSelect = document.getElementById('collection-default-grade');

    function formatMoney(amount) {
        return '$' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function rowGrades(row) {
        var grades = [];
        row.querySelectorAll('.collection-item-grade').forEach(function (select) {
            if (select.value) {
                grades.push(select.value);
            }
        });
        return grades;
    }

    function rowLineValue(row) {
        var want = row.querySelector('.collection-manage-want');
        if (want && want.checked) return null;

        var grades = rowGrades(row);
        if (grades.length === 0) return null;

        var numericValues = {};
        try {
            numericValues = JSON.parse(row.getAttribute('data-numeric-values') || '{}');
        } catch (e) {}

        var total = 0;
        var hasValue = false;

        grades.forEach(function (cond) {
            var unit = numericValues[cond];
            if (unit === null || unit === undefined) return;
            total += unit;
            hasValue = true;
        });

        return hasValue ? total : null;
    }

    function rowValueLabel(row, lineValue) {
        if (lineValue === null) return '--';

        var grades = rowGrades(row);
        if (grades.length === 0) return '--';

        var counts = {};
        grades.forEach(function (code) {
            counts[code] = (counts[code] || 0) + 1;
        });

        var unique = (window.collectionGradeCodes || []).filter(function (code) {
            return counts[code];
        });
        var displayValues = {};
        try {
            displayValues = JSON.parse(row.getAttribute('data-display-values') || '{}');
        } catch (e) {}

        if (unique.length === 1) {
            var cond = unique[0];
            var unitLabel = displayValues[cond] || formatMoney(lineValue / grades.length);
            if (grades.length > 1) {
                return formatMoney(lineValue) + ' (' + grades.length + ' × ' + unitLabel + ')';
            }
            return formatMoney(lineValue);
        }

        var parts = unique.map(function (code) {
            return counts[code] > 1 ? counts[code] + '×' + code : code;
        });

        return formatMoney(lineValue) + ' (' + parts.join(', ') + ')';
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

    document.querySelectorAll('.collection-manage-want').forEach(function (el) {
        el.addEventListener('change', recalculateSetTotal);
    });

    document.addEventListener('collection-items-changed', recalculateSetTotal);

    function rowIsEmpty(row) {
        var want = row.querySelector('.collection-manage-want');
        if (want && want.checked) return false;
        return row.querySelectorAll('[data-item-row]').length === 0;
    }

    function applyDefaultsToForm() {
        if (!countInput || !gradeSelect || !modeSelect) return;
        var count = countInput.value;
        var grade = gradeSelect.value;
        var fillAll = modeSelect.value === 'all';
        var rows = document.querySelectorAll('[data-collection-row]');
        var applied = 0;

        rows.forEach(function (row) {
            if (!fillAll && !rowIsEmpty(row)) return;

            var wantField = row.querySelector('.collection-manage-want');
            var container = row.querySelector('[data-collection-items]');

            if (wantField) wantField.checked = false;
            if (container && typeof window.collectionReplaceItems === 'function') {
                window.collectionReplaceItems(container, count, grade);
            }
            row.classList.add('collection-manage-row-has-entry');
            applied++;
        });

        recalculateSetTotal();

        if (applyBtn) {
            var original = applyBtn.textContent;
            applyBtn.textContent = 'Applied to ' + applied + ' rows';
            setTimeout(function () { applyBtn.textContent = original; }, 2000);
        }
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', applyDefaultsToForm);
    }

    recalculateSetTotal();
})();
</script>
@endif
@endsection

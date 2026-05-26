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

    @if ($selectedSet && $plates)
        <section class="collection-manage-set-header">
            <h2 class="collection-manage-set-title">{{ $setMeta->set_name }}</h2>
            <p class="collection-manage-set-meta">
                @if ($setMeta->company){{ $setMeta->company }} · @endif
                @if ($setMeta->year){{ $setMeta->year }} · @endif
                Set code {{ $setMeta->set_code }} · {{ number_format($setMeta->plate_count) }} catalog entries
            </p>
            <p class="collection-manage-pdf-note">
                Save your changes before downloading a PDF. The file reflects what is stored in your collection, ready to print on letter paper or attach to email.
            </p>
        </section>

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
                            <tr class="@if($entry) collection-manage-row-has-entry @endif">
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
@endsection

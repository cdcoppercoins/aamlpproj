@extends('layouts.app')

@section('title', 'Edit Collection Entry | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="auth-page set-width collection-edit-page">
    @php $plate = $item->plate; @endphp

    <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
        <ol class="gallery-breadcrumbs-list">
            <li><a href="{{ route('collection.index') }}">My Collection</a></li>
            <li aria-current="page">Edit entry</li>
        </ol>
    </nav>

    <h1>Edit collection entry</h1>
    <p class="auth-lead">
        <strong>{{ $plate->set_name }}</strong>
        @if ($plate->jurisdiction)
            — {{ strtoupper($plate->jurisdiction) }}
        @endif
        @if ($plate->variety_notes)
            <br>{{ $plate->variety_notes }}
        @endif
    </p>

    <form class="auth-form collection-edit-form" method="post" action="{{ route('collection.update', $item) }}">
        @csrf
        @method('PUT')

        <label class="auth-field">
            <span class="auth-label">Quantity</span>
            <input type="number" name="quantity" value="{{ old('quantity', $item->quantity) }}" min="1" max="9999" required>
        </label>

        <label class="auth-field">
            <span class="auth-label">Condition</span>
            <select name="condition">
                <option value="">— Not set —</option>
                @foreach ($conditions as $code => $label)
                    <option value="{{ $code }}" @selected(old('condition', $item->condition) === $code)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="auth-field">
            <span class="auth-label">Date acquired</span>
            <input type="date" name="acquired_date" value="{{ old('acquired_date', $item->acquired_date?->format('Y-m-d')) }}">
        </label>

        <label class="auth-field">
            <span class="auth-label">Price paid (each or total)</span>
            <input type="number" name="price_paid" value="{{ old('price_paid', $item->price_paid) }}" min="0" step="0.01" placeholder="0.00">
        </label>

        <label class="auth-field">
            <span class="auth-label">Storage location</span>
            <input type="text" name="storage_location" value="{{ old('storage_location', $item->storage_location) }}" maxlength="128" placeholder="e.g. Binder 3, page 12">
        </label>

        <label class="auth-field">
            <span class="auth-label">Private notes</span>
            <textarea name="notes" rows="4" maxlength="5000">{{ old('notes', $item->notes) }}</textarea>
        </label>

        <label class="auth-checkbox">
            <input type="checkbox" name="is_wanted" value="1" @checked(old('is_wanted', $item->is_wanted))>
            On my want list (not yet owned)
        </label>

        <p class="auth-actions">
            <button type="submit" class="home-primary-btn">Save changes</button>
            <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.index') }}">Cancel</a>
        </p>
    </form>
</div>
@endsection

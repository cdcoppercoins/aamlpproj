@extends('layouts.app')

@section('title', 'Edit Collection Entry | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page auth-page collection-edit-page">
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

    @php $item->setRelation('plate', $plate); @endphp
    @if (! $item->is_wanted && $item->ownedLineValue() !== null)
        <p class="collection-edit-catalog-value">
            Catalog value at {{ $item->gradeSummary() }} (private): <strong>{{ $item->formattedOwnedLineValue() }}</strong>
        </p>
    @endif

    <form class="auth-form collection-edit-form" method="post" action="{{ route('collection.update', $item) }}">
        @csrf
        @method('PUT')

        <div class="auth-field">
            <span class="auth-label">Owned items</span>
            <p class="auth-field-help">Each physical plate gets a permanent, auto-assigned serial (plate year + 5 digits, e.g. 193800001). Serial numbers are unique site-wide and cannot be changed or reused.</p>
            @include('components.collection-items-editor', [
                'namePrefix' => 'owned_items',
                'itemRows' => old('owned_items', $item->ownedItemsFormRows()),
                'gradeOptions' => $grades,
                'plateLabel' => $plate->jurisdiction ? strtoupper($plate->jurisdiction) : 'plate',
            ])
        </div>

        <label class="auth-field">
            <span class="auth-label">Entry notes</span>
            <textarea name="notes" rows="3" maxlength="5000" placeholder="Notes about this catalog listing in your collection">{{ old('notes', $item->notes) }}</textarea>
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

@push('scripts')
    @include('components.collection-items-script')
@endpush

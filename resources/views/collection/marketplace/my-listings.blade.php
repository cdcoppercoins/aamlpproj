@extends('layouts.app')

@section('title', 'My Listings | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page collection-marketplace-page">
    <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
        <ol class="gallery-breadcrumbs-list">
            <li><a href="{{ route('collection.index') }}">My Collection</a></li>
            <li><a href="{{ route('collection.marketplace.index') }}">Marketplace</a></li>
            <li aria-current="page">My listings</li>
        </ol>
    </nav>

    <h1 class="home-title">My listings</h1>
    <p class="home-lead">Pieces you have offered for sale or trade. When you list a plate, members who want that catalog entry are shown below.</p>

    @if ($listings->isEmpty())
        <p class="collection-empty">You have no active listings. Open any owned item in <strong>Edit entry</strong> and set <strong>Offer</strong> to For sale, For trade, or Sale or trade.</p>
    @else
        <ul class="collection-my-listings">
            @foreach ($listings as $listing)
                @php
                    $plate = $listing->collectionItem->plate;
                    $matches = $wantMatchesByPlate[$plate->id] ?? collect();
                @endphp
                <li class="collection-my-listing-card">
                    <h2 class="collection-my-listing-title">
                        {{ $plate->set_name }} — {{ $plate->jurisdiction ? strtoupper($plate->jurisdiction) : 'Plate' }}
                    </h2>
                    <p class="collection-my-listing-meta">
                        {{ $listing->listingLabel() }}
                        @if ($listing->grade) · {{ $listing->grade }} @endif
                        @if ($listing->serial_number) · #{{ $listing->serial_number }} @endif
                    </p>
                    @if ($listing->listing_notes)
                        <p class="collection-my-listing-notes">{{ $listing->listing_notes }}</p>
                    @endif
                    <p class="collection-my-listing-want">
                        @if ($matches->isEmpty())
                            No other members have this plate on their want list yet.
                        @else
                            <strong>{{ $matches->count() }} want-list {{ Str::plural('match', $matches->count()) }}:</strong>
                            {{ $matches->pluck('username')->join(', ') }}
                        @endif
                    </p>
                    <p class="collection-my-listing-actions">
                        <a class="gallery-result-btn" href="{{ route('collection.edit', $listing->collectionItem) }}">Edit listing</a>
                    </p>
                </li>
            @endforeach
        </ul>
    @endif

    <p class="collection-marketplace-toolbar">
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.marketplace.index') }}">Browse marketplace</a>
    </p>
</div>
@endsection

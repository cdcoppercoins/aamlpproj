@extends('layouts.app')

@section('title', 'My Collection | MiniLicensePlates.com')

@section('meta_description', 'Your personal miniature license plate collection — quantities, conditions, and notes.')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page">
    <section class="home-hero gallery-hero">
        <p class="home-welcome">Signed in as {{ auth()->user()->username }}</p>
        <h1 class="home-title">My collection</h1>
        <p class="home-lead">
            {{ number_format($stats['distinct_owned']) }} catalog {{ Str::plural('entry', $stats['distinct_owned']) }} owned
            ({{ number_format($stats['owned']) }} {{ Str::plural('plate', $stats['owned']) }} total)
            @if ($stats['wanted'] > 0)
                · {{ number_format($stats['wanted']) }} on want list
            @endif
        </p>
    </section>

    <section class="collection-toolbar" aria-label="Collection filters">
        <div class="collection-filter-tabs">
            <a href="{{ route('collection.index', ['filter' => 'owned']) }}"
               class="collection-filter-tab @if($filter === 'owned') is-active @endif">Owned</a>
            <a href="{{ route('collection.index', ['filter' => 'wanted']) }}"
               class="collection-filter-tab @if($filter === 'wanted') is-active @endif">Want list</a>
            <a href="{{ route('collection.index', ['filter' => 'all']) }}"
               class="collection-filter-tab @if($filter === 'all') is-active @endif">All</a>
        </div>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.manage') }}">Edit by set</a>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('search') }}">Search catalog</a>
        <form method="post" action="{{ route('logout') }}" class="collection-logout-form">
            @csrf
            <button type="submit" class="collection-logout-btn">Sign out</button>
        </form>
    </section>

    @if ($items->count() === 0)
        <section class="collection-empty">
            <p>
                @if ($filter === 'wanted')
                    Your want list is empty. Use <strong>Add to want list</strong> on catalog search results.
                @else
                    No plates in your collection yet. Use <strong>Edit by set</strong> to fill in a whole run at once, or add plates from <a href="{{ route('search') }}">Catalog Search</a>.
                @endif
            </p>
            <p>
                <a class="home-primary-btn" href="{{ route('collection.manage') }}">Edit by set</a>
                <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('search') }}">Catalog Search</a>
        </section>
    @else
        <section class="collection-list" aria-label="Collection entries">
            @foreach ($items as $item)
                @php
                    $plate = $item->plate;
                    $frontUrl = $plate->frontImageUrl();
                    $placeholder = asset('plate_missing.png');
                @endphp
                <article class="collection-row">
                    <div class="collection-row-image">
                        <img src="{{ $frontUrl ?? $placeholder }}"
                             alt="{{ $plate->jurisdiction ?? 'Mini license plate' }}"
                             onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                    </div>
                    <div class="collection-row-body">
                        <h2 class="collection-row-title">
                            {{ $plate->set_name }}
                            @if ($plate->jurisdiction)
                                — {{ strtoupper($plate->jurisdiction) }}
                            @endif
                        </h2>
                        <p class="collection-row-meta">
                            @if ($plate->company){{ $plate->company }} · @endif
                            @if ($plate->year){{ $plate->year }} · @endif
                            @if ($plate->variety_notes){{ $plate->variety_notes }} · @endif
                            @if ($item->is_wanted)<strong>Want list</strong>@else<strong>Owned</strong>@endif
                        </p>
                        <dl class="collection-row-stats">
                            <div>
                                <dt>Qty</dt>
                                <dd>{{ $item->quantity }}</dd>
                            </div>
                            <div>
                                <dt>Condition</dt>
                                <dd>{{ $item->conditionLabel() ?? '—' }}</dd>
                            </div>
                            @if ($item->acquired_date)
                                <div>
                                    <dt>Acquired</dt>
                                    <dd>{{ $item->acquired_date->format('M j, Y') }}</dd>
                                </div>
                            @endif
                            @if ($item->price_paid !== null)
                                <div>
                                    <dt>Paid</dt>
                                    <dd>${{ number_format((float) $item->price_paid, 2) }}</dd>
                                </div>
                            @endif
                            @if ($item->storage_location)
                                <div>
                                    <dt>Location</dt>
                                    <dd>{{ $item->storage_location }}</dd>
                                </div>
                            @endif
                        </dl>
                        @if ($item->notes)
                            <p class="collection-row-notes">{{ $item->notes }}</p>
                        @endif
                        <p class="collection-row-actions">
                            <a class="gallery-result-btn" href="{{ route('collection.edit', $item) }}">Edit</a>
                            <a class="gallery-result-btn" href="{{ route('gallery.show', $plate->set_name) }}">View set</a>
                            <form method="post" action="{{ route('collection.destroy', $item) }}" class="collection-inline-form"
                                  onsubmit="return confirm('Remove this entry from your collection?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="collection-remove-btn">Remove</button>
                            </form>
                        </p>
                    </div>
                </article>
            @endforeach
        </section>

        @if ($items->hasPages())
            @include('components.gallery-pagination', ['results' => $items])
        @endif
    @endif
</div>
@endsection

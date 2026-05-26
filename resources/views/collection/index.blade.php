@extends('layouts.app')

@section('title', 'My Collection | MiniLicensePlates.com')

@section('meta_description', 'Your personal miniature license plate collection — organized by set with optional sharing to other members.')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page">
    <section class="home-hero gallery-hero">
        <div class="collection-hero-user">
            @if (auth()->user()->profileImageUrl())
                <img src="{{ auth()->user()->profileImageUrl() }}"
                     alt=""
                     class="collection-hero-avatar">
            @endif
            <div>
                <p class="home-welcome">Signed in as {{ auth()->user()->username }}</p>
                <h1 class="home-title">My collection</h1>
            </div>
        </div>
        <p class="home-lead">
            {{ number_format($stats['set_count']) }} {{ Str::plural('set', $stats['set_count']) }} with entries
            · {{ number_format($stats['owned']) }} {{ Str::plural('plate', $stats['owned']) }} owned
            @if ($stats['wanted'] > 0)
                · {{ number_format($stats['wanted']) }} on want list
            @endif
            @if ($stats['catalog_total'] !== null)
                · <span class="collection-catalog-total-label">Catalog value (private): {{ \App\Models\Plate::formatCatalogTotal($stats['catalog_total']) }}</span>
            @endif
        </p>
    </section>

    <section class="collection-toolbar" aria-label="Collection actions">
        <a class="home-primary-btn" href="{{ route('collection.manage') }}">Add or edit a set</a>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('search') }}">Catalog search</a>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('profile.edit') }}">Profile</a>
        <form method="post" action="{{ route('logout') }}" class="collection-logout-form">
            @csrf
            <button type="submit" class="collection-logout-btn">Sign out</button>
        </form>
    </section>

    @if ($setSummaries->isEmpty())
        <section class="collection-empty">
            <p>No sets in your collection yet. Use <strong>Add or edit a set</strong> to record plates from a full catalog run, or add individual plates from <a href="{{ route('search') }}">Catalog Search</a>.</p>
            <p>
                <a class="home-primary-btn" href="{{ route('collection.manage') }}">Add or edit a set</a>
            </p>
        </section>
    @else
        <section class="collection-sets" aria-label="Your sets">
            <h2 class="home-section-title">Your sets</h2>
            <p class="collection-sets-intro">
                Each row is a catalog set where you have recorded plates. Choose <strong>Public</strong> to let other signed-in members view that set&rsquo;s contents (not your private notes or storage locations).
            </p>

            <ul class="collection-set-list">
                @foreach ($setSummaries as $set)
                    <li class="collection-set-card">
                        <div class="collection-set-card-main">
                            <h3 class="collection-set-card-title">
                                <a href="{{ route('collection.manage', ['set_name' => $set->set_name]) }}">{{ $set->set_name }}</a>
                            </h3>
                            <p class="collection-set-card-meta">
                                @if ($set->company){{ $set->company }} · @endif
                                @if ($set->year){{ $set->year }} · @endif
                                {{ $set->set_code }}
                            </p>
                            <p class="collection-set-card-stats">
                                {{ number_format($set->entry_count) }} {{ Str::plural('entry', $set->entry_count) }}
                                · {{ number_format($set->owned_qty) }} owned
                                @if ($set->wanted_count > 0)
                                    · {{ number_format($set->wanted_count) }} wanted
                                @endif
                                @if ($set->catalog_total !== null)
                                    · Catalog value {{ \App\Models\Plate::formatCatalogTotal($set->catalog_total) }}
                                @endif
                            </p>
                        </div>
                        <div class="collection-set-card-actions">
                            <a class="gallery-result-btn" href="{{ route('collection.manage', ['set_name' => $set->set_name]) }}">Edit set</a>
                            <a class="gallery-result-btn" href="{{ route('collection.manage.pdf', ['set_name' => $set->set_name, 'scope' => 'mine']) }}">PDF</a>
                            <form class="collection-visibility-form" method="post" action="{{ route('collection.set.visibility', $set->set_code) }}">
                                @csrf
                                @method('PUT')
                                <label class="collection-visibility-label">
                                    <span class="collection-visibility-text">Visibility</span>
                                    <select name="is_public" class="collection-visibility-select" onchange="this.form.submit()">
                                        <option value="0" @selected((int) $set->is_public === 0)>Private</option>
                                        <option value="1" @selected((int) $set->is_public === 1)>Public</option>
                                    </select>
                                </label>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="collection-members" aria-label="Public member collections">
        <h2 class="home-section-title">Public member collections</h2>
        <p class="collection-sets-intro">
            Collectors who marked one or more sets as public. Totals reflect owned quantities in their public sets only.
        </p>

        @if ($publicCollectors->isEmpty())
            <p class="collection-members-empty">No public collections from other members yet.</p>
        @else
            <ul class="collection-member-list">
                @foreach ($publicCollectors as $collector)
                    <li class="collection-member-card">
                        <a href="{{ route('collection.members.show', $collector->username) }}" class="collection-member-link">
                            @if ($collector->profile_image)
                                <img src="{{ asset('storage/' . $collector->profile_image) }}"
                                     alt=""
                                     class="collection-member-avatar">
                            @else
                                <span class="collection-member-avatar collection-member-avatar-placeholder" aria-hidden="true">{{ strtoupper(substr($collector->name, 0, 1)) }}</span>
                            @endif
                            <span class="collection-member-info">
                                <span class="collection-member-name">{{ $collector->name }}</span>
                                <span class="collection-member-username">@{{ $collector->username }}</span>
                                <span class="collection-member-stats">
                                    {{ number_format($collector->owned_qty) }} {{ Str::plural('plate', $collector->owned_qty) }} owned
                                    · {{ number_format($collector->public_set_count) }} public {{ Str::plural('set', $collector->public_set_count) }}
                                </span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection

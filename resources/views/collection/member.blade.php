@extends('layouts.app')

@section('title', $member->name . ' — Public Collection | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page">
    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('collection.index') }}">My Collection</a></li>
                <li aria-current="page">{{ $member->username }}</li>
            </ol>
        </nav>

        <div class="collection-hero-user">
            @if ($member->profileImageUrl())
                <img src="{{ $member->profileImageUrl() }}" alt="" class="collection-hero-avatar">
            @endif
            <div>
                <p class="home-welcome">Public collection</p>
                <h1 class="home-title">{{ $member->name }}</h1>
            </div>
        </div>
        <p class="home-lead">
            {{ $member->username }}
            · {{ number_format($totalOwned) }} {{ Str::plural('plate', $totalOwned) }} owned in public sets
            · {{ number_format($publicSets->count()) }} {{ Str::plural('set', $publicSets->count()) }}
        </p>
    </section>

    <section class="collection-sets" aria-label="Public sets">
        <h2 class="home-section-title">Public sets</h2>

        <ul class="collection-set-list">
            @foreach ($publicSets as $set)
                <li class="collection-set-card">
                    <div class="collection-set-card-main">
                        <h3 class="collection-set-card-title">
                            <a href="{{ route('collection.members.show', ['username' => $member->username, 'set_name' => $set->set_name]) }}">{{ $set->set_name }}</a>
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
                        </p>
                    </div>
                    <div class="collection-set-card-actions">
                        <a class="gallery-result-btn" href="{{ route('collection.members.show', ['username' => $member->username, 'set_name' => $set->set_name]) }}">View contents</a>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</div>
@endsection

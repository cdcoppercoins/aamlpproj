@extends('layouts.app')

@section('title', 'Mini License Plate Catalog & Price Guide | MiniLicensePlates.com')

@section('meta_description', 'Identify and value miniature license plates from Post, Topps, General Mills, Goudey, and other cereal and gum premiums. ' . number_format($plateCount) . '+ subjects cataloged — browse sets, search by state and year, print checklists.')

@section('canonical_url', route('home'))

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'MiniLicensePlates.com',
    'url' => route('home'),
    'description' => 'Catalog and price guide for miniature license plate premiums from Post, Topps, General Mills, and other issuers.',
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => route('search') . '?search=1&jurisdiction={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@push('scripts')
<script src="{{ asset('js/home-hero.js') }}" defer></script>
@endpush

@section('content')
<div class="home-page home-page--flush-hero">
    @include('components.home-hero-rotator', [
        'heroSlides' => $heroSlides,
        'heroIntervalMs' => $heroIntervalMs,
    ])

    <section class="home-hero">
        <p class="home-welcome">MiniLicensePlates.com</p>
        <h1 class="home-title">Mini license plate catalog and collector&rsquo;s guide</h1>
        <p class="home-lead">
            A visual reference to the small plate toys packaged with candy, gum, and cereal from the 1930s through today.
            Issuers such as Post, Topps, General Mills, and Goudey used these premiums to reach collectors building complete state and
            topical sets.
        </p>
    </section>

    <section class="home-stat" aria-label="Collection size">
        <p class="home-stat-number">{{ number_format($plateCount) }}+</p>
        <p class="home-stat-label">subjects in the catalog</p>
    </section>

    <section class="home-mission">
        <div class="home-mission-content">
            <div class="home-mission-text">
                <h2 class="home-section-title">What this site offers</h2>
                <p>
                    The database lists more than <strong>{{ number_format($plateCount) }} distinct subjects</strong> — individual plates,
                    varieties, and catalog entries from dozens of issued sets. Use the
                    <a href="{{ route('gallery') }}">Gallery</a> to browse set images, or
                    <a href="{{ route('search') }}">Catalog Search</a> to filter by year, jurisdiction, issuer, and set type.
                </p>
                <p>
                    Each listing includes catalog values by grade so you can research what a plate is worth before you buy, sell, or trade.
                    Search results can be turned into a <strong>printable checklist</strong> for shows, want lists, or inventory at home.
                </p>
                <p>
                    We are also building a section on the <strong>history of these premiums</strong> — how they were designed, manufactured,
                    and inserted into product packaging — with more on that in our <a href="{{ route('history') }}">History</a> pages.
                </p>
                <p>
                    An <strong>on-site store</strong> is planned so collectors can connect reference information with plates offered for sale
                    in one place.
                </p>
            </div>
            <figure class="home-mission-image">
                <img src="{{ asset('blue_back_composite_img_sm.jpg') }}"
                     alt="1953 Wheaties cereal set — grid of miniature state license plates"
                     class="home-mission-img">
            </figure>
        </div>
    </section>

    <section class="home-features" aria-labelledby="home-features-title">
        <h2 id="home-features-title" class="home-section-title home-features-title">Tools for collectors</h2>
        <div class="home-features-grid">
        <div class="home-feature">
            <h3>
                <span class="home-feature-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M4 5a2 2 0 0 1 2-2h3v4H6v11H4V5zm7-2h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7V3zm2 2v14h5V5h-5zM6 7h1v2H6V7zm0 4h1v2H6v-2zm0 4h1v2H6v-2z"/></svg>
                </span>
                Set gallery
            </h3>
            <div class="home-feature-copy">
                <p>
                    View plate photos organized by issued set — a quick way to identify which product a plate came from and
                    what the complete run looks like.
                </p>
            </div>
            <div class="home-feature-action">
                <a class="home-primary-btn home-feature-btn" href="{{ route('gallery') }}">Browse the Gallery</a>
            </div>
        </div>
        <div class="home-feature">
            <h3>
                <span class="home-feature-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10.5 3a7.5 7.5 0 1 1 0 15 7.42 7.42 0 0 1-3.36-.8L4 19l1.8-3.14A7.47 7.47 0 0 1 3 10.5 7.5 7.5 0 0 1 10.5 3zm0 2a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11zm8.5 13.5a1 1 0 0 1 1 1v1.5H22v2h-1.5V22h-2v-1.5H17v-2h1.5V18a1 1 0 0 1 1-1z"/></svg>
                </span>
                Catalog search
            </h3>
            <div class="home-feature-copy">
                <p>
                    Filter thousands of listings by state, province, year, company, or set type, then review results with
                    pricing in a collector-friendly layout.
                </p>
            </div>
            <div class="home-feature-action">
                <a class="home-primary-btn home-feature-btn" href="{{ route('search') }}">Catalog Search</a>
            </div>
        </div>
        <div class="home-feature">
            <h3>
                <span class="home-feature-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 3h10a2 2 0 0 1 2 2v1h2v2h-1v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8H3V6h2V5a2 2 0 0 1 2-2zm10 5H7v11h10V8zM9 10h2v2H9v-2zm4 0h2v2h-2v-2zm-4 4h2v2H9v-2zm4 0h2v2h-2v-2z"/></svg>
                </span>
                Printable checklists
            </h3>
            <div class="home-feature-copy">
                <p>
                    Build a focused list of what you own or still need, then print it for your next show, trade meet, or
                    mail-order search.
                </p>
            </div>
            <div class="home-feature-action">
                @auth
                    <a class="home-primary-btn home-feature-btn" href="{{ route('collection.index') }}">My Collection</a>
                @else
                    <a class="home-primary-btn home-feature-btn" href="{{ route('login') }}">My Collection</a>
                @endauth
            </div>
        </div>
        </div>
    </section>

    <section class="home-brands" aria-label="Brands featured on this site">
        <img src="{{ asset('brands.jpg') }}"
             alt="Brands that issued miniature license plates: Baker's, General Mills, Goudey, Leader, Post, Quaker, Topps, and others"
             class="home-brands-img">
    </section>

    <section class="home-actions">
        <p class="home-secondary-links">
            New here? Read <a href="{{ route('pages.show', 'about') }}">About</a> · Explore <a href="{{ route('history') }}">History</a>
            · <a href="{{ route('contribute') }}">Contribute</a>
        </p>
    </section>
</div>
@endsection

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

@section('content')
<div class="home-page">
    <section class="home-top-banner" aria-label="Featured miniature license plates">
        <img src="{{ asset('home_top_banner.jpg') }}"
             alt="Miniature license plates from Post, Topps, and cereal premium sets"
             class="home-top-banner-img">
    </section>

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
            <h3>Set gallery</h3>
            <p>
                View plate photos organized by issued set — a quick way to identify which product a plate came from and
                what the complete run looks like.
            </p>
        </div>
        <div class="home-feature">
            <h3>Catalog search</h3>
            <p>
                Filter thousands of listings by state, province, year, company, or set type, then review results with
                pricing in a collector-friendly layout.
            </p>
        </div>
        <div class="home-feature">
            <h3>Printable checklists</h3>
            <p>
                Build a focused list of what you own or still need, then print it for your next show, trade meet, or
                mail-order search.
            </p>
        </div>
        </div>
    </section>

    <section class="home-brands" aria-label="Brands featured on this site">
        <img src="{{ asset('brands.jpg') }}"
             alt="Brands that issued miniature license plates: Baker's, General Mills, Goudey, Leader, Post, Quaker, Topps, and others"
             class="home-brands-img">
    </section>

    <section class="home-actions">
        <a class="home-primary-btn" href="{{ route('gallery') }}">Browse the Gallery</a>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('search') }}">Catalog Search</a>
        <p class="home-secondary-links">
            New here? Read <a href="{{ route('about') }}">About</a> · Explore <a href="{{ route('history') }}">History</a>
            · <a href="{{ route('contribute') }}">Contribute</a>
        </p>
    </section>
</div>
@endsection

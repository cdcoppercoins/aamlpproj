@extends('layouts.app')

@section('title', 'Mini License Plates Catalog & Gallery | MiniLicensePlates.com')

@section('meta_description', 'MiniLicensePlates.com is the largest visual guide to miniature license plate premiums from Post, Topps, General Mills, and other issuers. Browse ' . number_format($plateCount) . '+ cataloged plates, search by set and jurisdiction, and view collector values.')

@section('canonical_url', route('home'))

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'MiniLicensePlates.com',
    'url' => route('home'),
    'description' => 'Visual reference and pricing catalog for miniature license plates from candy, gum, and cereal premiums.',
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
    <section class="home-top-banner" aria-label="Featured mini license plates">
        <img src="{{ asset('home_top_banner.jpg') }}"
             alt="A selection of miniature license plate premiums from various sets"
             class="home-top-banner-img">
    </section>

    <section class="home-hero">
        <p class="home-welcome">Welcome to the reference</p>
        <h1 class="home-title">MiniLicensePlates.com</h1>
        <p class="home-lead">
            This is the largest and most complete visual guide to the miniature license plates packaged with candy, gum,
            cereal, and other products. Manufacturers created these premiums to reach license plate enthusiasts—encouraging
            customers to buy the product and collect every plate in the set.
        </p>
    </section>

    <section class="home-stat" aria-label="Collection size">
        <p class="home-stat-number">{{ number_format($plateCount) }}+</p>
        <p class="home-stat-label">mini license plate subjects cataloged</p>
    </section>

    <section class="home-mission">
        <div class="home-mission-content">
            <div class="home-mission-text">
                <h2 class="home-section-title">Project statement</h2>
                <p>
                    This site documents more than <strong>{{ number_format($plateCount) }} distinct subjects</strong> in our database —
                    each representing a plate type, variety, or listing in the collector’s guide. Our goal is to provide the most
                    complete method available to <strong>search, browse, and list</strong> these items by set, jurisdiction, issuer,
                    year, and other catalog criteria.
                </p>
                <p>
                    Collectors can use search results to build a focused view of what they own or still need, then
                    <strong>create and print personal checklists</strong> from those results for use at shows, in trade, or at home.
                </p>
                <p>
                    The site also includes a dedicated section on the <strong>history of miniature license plate premiums</strong>
                    — how they were designed, manufactured, and packaged with candy, gum, cereal, and other products as promotions
                    for collectors and consumers.
                </p>
                <p>
                    We are also working toward offering many of the listed miniature license plates in an
                    <strong>upcoming store right here on the website</strong>, so collectors can find reference information and
                    available pieces in one place.
                </p>
            </div>
            <figure class="home-mission-image">
                <img src="{{ asset('blue_back_composite_img_sm.jpg') }}"
                     alt="Grid of miniature license plates from the 1953 Wheaties cereal set"
                     class="home-mission-img">
            </figure>
        </div>
    </section>

    <section class="home-features" aria-label="What this site offers">
        <div class="home-feature">
            <h3>Comprehensive catalog</h3>
            <p>
                Thousands of documented subjects with set, jurisdiction, and variety data — the foundation for serious
                collecting and research.
            </p>
        </div>
        <div class="home-feature">
            <h3>Search &amp; list</h3>
            <p>
                Find plates by the criteria that matter to you, then review results in a clear, catalog-style listing
                built for collectors.
            </p>
        </div>
        <div class="home-feature">
            <h3>Printable checklists</h3>
            <p>
                Turn any search into a checklist you can print and take with you — a practical tool for building and
                completing your collection.
            </p>
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

@extends('layouts.app')

@section('title', 'Search the Mini License Plate Catalog | MiniLicensePlates.com')

@section('meta_description', 'Search the miniature license plate catalog by year, jurisdiction, set name, company, and set type. View catalog values and printable checklist results for Post, Topps, and other premiums.')

@section('canonical_url', route('search'))

@if ($hasSearch)
@section('robots', 'noindex, follow')
@endif

@section('content')
<div class="home-page gallery-page search-page">
    <section class="home-hero gallery-hero">
        <div class="gallery-hero-row">
            <div class="gallery-hero-copy">
                <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
                    <ol class="gallery-breadcrumbs-list">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li aria-current="page">Catalog Search</li>
                    </ol>
                </nav>
                <p class="home-welcome">Search the catalog</p>
                <h1 class="home-title">Catalog Search</h1>
            </div>
            <div class="gallery-site-notice" role="note">
                <p>
                    Please understand that this website is a huge project which requires thousands of images,
                    many of which are yet to be included. Pardon our incompleteness while we try to provide
                    what we have accomplished to date. Thank you.
                </p>
            </div>
        </div>
    </section>

    <section id="gallery-search-panel"
             class="gallery-search-panel @if ($hasSearch && $results && $results->count() > 0) is-collapsed @endif"
             aria-label="Search filters">
        <p class="home-lead gallery-search-intro">
            Search {{ number_format(intdiv($totalCount, 50) * 50) }}+ catalog listings by year, jurisdiction, set, company, or set type.
            Leave any field at <strong>All</strong> to include every value for that criterion. Set type checkboxes can be combined.
        </p>
        <h2 class="home-section-title">Search criteria</h2>
        <form class="gallery-search-form" method="GET" action="{{ route('search') }}">
            <input type="hidden" name="search" value="1">

            <div class="gallery-search-grid">
                <label class="gallery-field">
                    <span class="gallery-field-label">Year</span>
                    <select name="year" class="gallery-select">
                        <option value="">All years</option>
                        @foreach ($filterOptions['years'] as $year)
                            <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="gallery-field">
                    <span class="gallery-field-label">Jurisdiction (state/prov/country)</span>
                    <select name="jurisdiction" class="gallery-select">
                        <option value="">All jurisdictions</option>
                        @foreach ($filterOptions['jurisdictions'] as $jurisdiction)
                            <option value="{{ $jurisdiction }}" @selected(($filters['jurisdiction'] ?? '') == $jurisdiction)>{{ $jurisdiction }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="gallery-field">
                    <span class="gallery-field-label">Set name</span>
                    <select name="set_name" class="gallery-select">
                        <option value="">All sets</option>
                        @foreach ($filterOptions['setNames'] as $setName)
                            <option value="{{ $setName }}" @selected(($filters['set_name'] ?? '') == $setName)>{{ $setName }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="gallery-field">
                    <span class="gallery-field-label">Company</span>
                    <select name="company" class="gallery-select">
                        <option value="">All companies</option>
                        @foreach ($filterOptions['companies'] as $company)
                            <option value="{{ $company }}" @selected(($filters['company'] ?? '') == $company)>{{ $company }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <fieldset class="gallery-field gallery-field--set-types">
                <legend class="gallery-field-label">Set type</legend>
                <div class="gallery-set-type-options">
                    @foreach ($setTypeOptions as $code => $label)
                        <label class="gallery-set-type-option">
                            <input type="checkbox"
                                   name="set_types[]"
                                   value="{{ $code }}"
                                   @checked(in_array($code, $filters['set_types'] ?? [], true))>
                            <span>{{ $code }} - {{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="gallery-search-actions">
                <button type="submit" class="home-primary-btn gallery-search-btn">Search catalog</button>
                <a class="gallery-clear-link" href="{{ route('search') }}">Clear filters</a>
            </div>
        </form>
    </section>

    @include('components.gallery-banner-placeholder')

    @if ($hasSearch && $results)
        @php
            $selectedSetTypes = collect($filters['set_types'] ?? [])
                ->map(fn ($code) => $code . ' - ' . ($setTypeOptions[$code] ?? $code))
                ->implode(', ');

            $activeFilters = collect([
                'year' => $filters['year'] ?? null,
                'jurisdiction' => $filters['jurisdiction'] ?? null,
                'set name' => $filters['set_name'] ?? null,
                'company' => $filters['company'] ?? null,
                'set type' => $selectedSetTypes !== '' ? $selectedSetTypes : null,
            ])->filter();
        @endphp
        <section class="gallery-results" aria-label="Search results">
            <div class="gallery-results-toolbar">
                <div class="gallery-results-header">
                    <h2 class="gallery-results-title">Search Results:</h2>
                    @if ($results->total() === 0)
                        <p class="gallery-results-summary">No plates matched your search criteria.</p>
                        <p class="gallery-results-note">Try broadening your search or set one or more fields back to All.</p>
                    @else
                        <p class="gallery-results-summary">
                            @if ($activeFilters->isNotEmpty())
                                You searched for
                                @foreach ($activeFilters as $label => $value)
                                    <strong>{{ $value }}</strong>@if (!$loop->last), @endif
                                @endforeach
                                —
                            @endif
                            Now viewing results {{ number_format($results->firstItem()) }}–{{ number_format($results->lastItem()) }}
                            of {{ number_format($results->total()) }} total results.
                        </p>
                    @endif
                </div>

                <div class="gallery-results-toolbar-actions">
                    @if ($results->count() > 0)
                        <div class="gallery-view-toggle" role="group" aria-label="Results view">
                            <button type="button"
                                    class="gallery-view-btn"
                                    data-view="list"
                                    aria-label="List view"
                                    aria-pressed="false">
                                <svg class="gallery-view-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="4" y="5" width="16" height="2.5" rx="1"></rect>
                                    <rect x="4" y="10.75" width="16" height="2.5" rx="1"></rect>
                                    <rect x="4" y="16.5" width="16" height="2.5" rx="1"></rect>
                                </svg>
                            </button>
                            <button type="button"
                                    class="gallery-view-btn is-active"
                                    data-view="grid"
                                    aria-label="Grid view"
                                    aria-pressed="true">
                                <svg class="gallery-view-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="4" y="4" width="7" height="7" rx="1"></rect>
                                    <rect x="13" y="4" width="7" height="7" rx="1"></rect>
                                    <rect x="4" y="13" width="7" height="7" rx="1"></rect>
                                    <rect x="13" y="13" width="7" height="7" rx="1"></rect>
                                </svg>
                            </button>
                        </div>
                        <button type="button"
                                id="gallerySearchToggle"
                                class="gallery-search-again-btn"
                                aria-expanded="false"
                                aria-controls="gallery-search-panel">
                            Try another search
                        </button>
                    @endif
                </div>
            </div>

            @if ($results->count() > 0)
                @if ($results->hasPages())
                    @include('components.gallery-pagination', ['results' => $results])
                @endif

                <div id="galleryResultsContainer" class="gallery-results-container is-grid">
                    @foreach ($results as $plate)
                        @include('components.gallery-result-card', [
                            'plate' => $plate,
                            'setCounts' => $setCounts,
                        ])
                    @endforeach
                    @php
                        $gridRowPad = (3 - ($results->count() % 3)) % 3;
                    @endphp
                    @for ($i = 0; $i < $gridRowPad; $i++)
                        <div class="gallery-result-card gallery-result-placeholder" aria-hidden="true"></div>
                    @endfor
                </div>

                @if ($results->hasPages())
                    @include('components.gallery-pagination', ['results' => $results])
                @endif

                @include('components.gallery-banner-placeholder')
            @endif
        </section>
    @endif
</div>

<div id="imageModal" class="modal">
    <span class="modal-close">&times;</span>
    <img id="modalImg" src="" alt="">
</div>

@include('components.modal_script')

@if ($hasSearch && $results && $results->count() > 0)
<script>
(function () {
    const panel = document.getElementById('gallery-search-panel');
    const toggleBtn = document.getElementById('gallerySearchToggle');

    if (panel && toggleBtn) {
        function setSearchPanelVisible(visible) {
            panel.classList.toggle('is-collapsed', !visible);
            toggleBtn.setAttribute('aria-expanded', visible ? 'true' : 'false');
            toggleBtn.textContent = visible ? 'Hide search' : 'Try another search';

            if (visible) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        toggleBtn.addEventListener('click', function () {
            setSearchPanelVisible(panel.classList.contains('is-collapsed'));
        });
    }

    const container = document.getElementById('galleryResultsContainer');
    const buttons = document.querySelectorAll('.gallery-view-btn');
    if (!container || !buttons.length) return;

    const storageKey = 'galleryResultsView';

    function setView(view) {
        const isList = view === 'list';
        container.classList.toggle('is-list', isList);
        container.classList.toggle('is-grid', !isList);

        buttons.forEach(function (btn) {
            const active = btn.dataset.view === view;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        try {
            localStorage.setItem(storageKey, view);
        } catch (e) {}
    }

    let savedView = 'grid';
    try {
        savedView = localStorage.getItem(storageKey) || 'grid';
    } catch (e) {}

    setView(savedView === 'list' ? 'list' : 'grid');

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setView(btn.dataset.view);
        });
    });
})();
</script>
@endif
@endsection

@extends('layouts.app')

@section('title', 'Admin — ' . $setMeta->set_name . ' | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.catalog.sets.index') }}">Catalog sets</a></li>
                <li aria-current="page">{{ $setMeta->set_name }}</li>
            </ol>
        </nav>
        <h1 class="home-title">{{ $setMeta->set_name }}</h1>
        <p class="home-lead">
            <code>{{ $setMeta->set_code }}</code>
            @if ($setMeta->company) · {{ $setMeta->company }} @endif
            @if ($setMeta->year) · {{ $setMeta->year }} @endif
            · {{ number_format($setMeta->plate_count) }} catalog {{ Str::plural('plate', $setMeta->plate_count) }}
        </p>
    </section>

    <section class="admin-toolbar">
        <a class="home-primary-btn" href="{{ route('admin.catalog.plates.create', $setMeta->set_code) }}">Add plate</a>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('admin.catalog.sets.edit', $setMeta->set_code) }}">Edit set</a>
        <a class="home-primary-btn home-primary-btn-secondary"
           href="{{ route('admin.catalog.import.create', ['set_code' => $setMeta->set_code, 'set_name' => $setMeta->set_name, 'company' => $setMeta->company, 'year' => $setMeta->year]) }}">Add plates (CSV)</a>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('gallery.show', urlencode($setMeta->set_name)) }}" target="_blank" rel="noopener">View in gallery</a>
    </section>

    <section class="admin-panel">
        <form class="admin-filter-form" method="get" action="{{ route('admin.catalog.sets.show', $setMeta->set_code) }}">
            <label class="admin-filter-field admin-filter-field-wide">
                <span class="auth-label">Search plates in this set</span>
                <input type="search"
                       name="q"
                       value="{{ $search ?? '' }}"
                       placeholder="Jurisdiction, serial, cat ref, variety">
            </label>
            <button type="submit" class="home-primary-btn admin-filter-btn">Search</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Photo</th>
                        <th scope="col">Jurisdiction</th>
                        <th scope="col">Serial</th>
                        <th scope="col">Cat ref</th>
                        <th scope="col">Variety</th>
                        <th scope="col">MT</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plates as $plate)
                        <tr>
                            <td class="admin-plate-thumb-cell">
                                @if ($plate->frontImageUrl())
                                    <img src="{{ $plate->frontImageUrl() }}" alt="" class="admin-plate-thumb">
                                @else
                                    <span class="admin-empty">No photo</span>
                                @endif
                            </td>
                            <td>{{ $plate->jurisdiction ?: '—' }}</td>
                            <td>{{ $plate->serial_number ?: '—' }}</td>
                            <td>{{ $plate->cat_ref ?: '—' }}</td>
                            <td>{{ $plate->variety_key ?: 'base' }}</td>
                            <td>{{ $plate->displayValue('value_mt') }}</td>
                            <td>
                                <a href="{{ route('admin.catalog.plates.edit', $plate) }}" class="admin-inline-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-empty-cell">No plates in this set yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($plates->hasPages())
            <div class="admin-pagination">
                @if ($plates->onFirstPage())
                    <span class="admin-pagination-disabled">Previous</span>
                @else
                    <a href="{{ $plates->previousPageUrl() }}" class="admin-inline-link">Previous</a>
                @endif
                <span class="admin-pagination-info">Page {{ $plates->currentPage() }} of {{ $plates->lastPage() }}</span>
                @if ($plates->hasMorePages())
                    <a href="{{ $plates->nextPageUrl() }}" class="admin-inline-link">Next</a>
                @else
                    <span class="admin-pagination-disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

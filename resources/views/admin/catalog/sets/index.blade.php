@extends('layouts.app')

@section('title', 'Admin — Catalog Sets | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <p class="home-welcome">Catalog CMS</p>
        <h1 class="home-title">Sets</h1>
        <p class="home-lead">Browse issued sets, add new ones, and manage catalog plates within each set.</p>
    </section>

    <section class="admin-toolbar">
        <a class="home-primary-btn" href="{{ route('admin.catalog.sets.create') }}">Add set</a>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('admin.catalog.import.create') }}">Import CSV</a>
    </section>

    <section class="admin-panel">
        <form class="admin-filter-form" method="get" action="{{ route('admin.catalog.sets.index') }}">
            <label class="admin-filter-field admin-filter-field-wide">
                <span class="auth-label">Search sets</span>
                <input type="search"
                       name="q"
                       value="{{ $search ?? '' }}"
                       placeholder="Set code, name, or company">
            </label>
            <button type="submit" class="home-primary-btn admin-filter-btn">Search</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Set code</th>
                        <th scope="col">Set name</th>
                        <th scope="col">Company</th>
                        <th scope="col">Year</th>
                        <th scope="col">Plates</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sets as $set)
                        <tr>
                            <td><code>{{ $set->set_code }}</code></td>
                            <td>{{ $set->set_name }}</td>
                            <td>{{ $set->company ?: '—' }}</td>
                            <td>{{ $set->year ?: '—' }}</td>
                            <td>{{ number_format($set->plate_count) }}</td>
                            <td>
                                <a href="{{ route('admin.catalog.sets.show', $set->set_code) }}" class="admin-inline-link">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty-cell">No sets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sets->hasPages())
            <div class="admin-pagination">
                @if ($sets->onFirstPage())
                    <span class="admin-pagination-disabled">Previous</span>
                @else
                    <a href="{{ $sets->previousPageUrl() }}" class="admin-inline-link">Previous</a>
                @endif
                <span class="admin-pagination-info">Page {{ $sets->currentPage() }} of {{ $sets->lastPage() }}</span>
                @if ($sets->hasMorePages())
                    <a href="{{ $sets->nextPageUrl() }}" class="admin-inline-link">Next</a>
                @else
                    <span class="admin-pagination-disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

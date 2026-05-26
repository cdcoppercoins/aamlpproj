@extends('layouts.app')

@section('title', 'Admin — Newsletter | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <p class="home-welcome">Site administration</p>
        <h1 class="home-title">Newsletter subscribers</h1>
        <p class="home-lead">Email addresses collected from the site footer sign-up form.</p>
    </section>

    <section class="admin-panel">
        <form class="admin-filter-form" method="get" action="{{ route('admin.newsletter.index') }}">
            <label class="admin-filter-field admin-filter-field-wide">
                <span class="auth-label">Search email</span>
                <input type="search"
                       name="q"
                       value="{{ $search ?? '' }}"
                       placeholder="name@example.com">
            </label>
            <button type="submit" class="home-primary-btn admin-filter-btn">Search</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Email</th>
                        <th scope="col">Subscribed</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscribers as $subscriber)
                        <tr>
                            <td>{{ $subscriber->email }}</td>
                            <td>{{ $subscriber->subscribed_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td>
                                <form method="post"
                                      action="{{ route('admin.newsletter.destroy', $subscriber) }}"
                                      class="admin-inline-form"
                                      onsubmit="return confirm('Remove this subscriber?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-inline-danger-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="admin-empty-cell">No subscribers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subscribers->hasPages())
            <div class="admin-pagination">
                @if ($subscribers->onFirstPage())
                    <span class="admin-pagination-disabled">Previous</span>
                @else
                    <a href="{{ $subscribers->previousPageUrl() }}" class="admin-inline-link">Previous</a>
                @endif

                <span class="admin-pagination-info">Page {{ $subscribers->currentPage() }} of {{ $subscribers->lastPage() }}</span>

                @if ($subscribers->hasMorePages())
                    <a href="{{ $subscribers->nextPageUrl() }}" class="admin-inline-link">Next</a>
                @else
                    <span class="admin-pagination-disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

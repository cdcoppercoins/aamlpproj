@extends('layouts.app')

@section('title', 'Admin — Members | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <p class="home-welcome">Site administration</p>
        <h1 class="home-title">Members</h1>
        <p class="home-lead">View accounts, grant admin access, block sign-in, or remove members.</p>
    </section>

    <section class="admin-panel">
        <form class="admin-filter-form" method="get" action="{{ route('admin.users.index') }}">
            <label class="admin-filter-field">
                <span class="auth-label">Search</span>
                <input type="search"
                       name="q"
                       value="{{ $search ?? '' }}"
                       placeholder="Username, email, or name">
            </label>
            <label class="admin-filter-field">
                <span class="auth-label">Status</span>
                <select name="status">
                    <option value="">All members</option>
                    <option value="active" @selected(request('status') === 'active')>Active only</option>
                    <option value="blocked" @selected(request('status') === 'blocked')>Blocked only</option>
                    <option value="admin" @selected(request('status') === 'admin')>Administrators</option>
                </select>
            </label>
            <button type="submit" class="home-primary-btn admin-filter-btn">Filter</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Username</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Collection</th>
                        <th scope="col">Status</th>
                        <th scope="col">Joined</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $member)
                        <tr>
                            <td>{{ $member->username }}</td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>{{ number_format($member->collection_items_count) }}</td>
                            <td>
                                @if ($member->is_blocked)
                                    <span class="admin-badge admin-badge-blocked">Blocked</span>
                                @elseif ($member->is_admin)
                                    <span class="admin-badge admin-badge-admin">Admin</span>
                                @else
                                    <span class="admin-badge admin-badge-active">Active</span>
                                @endif
                            </td>
                            <td>{{ $member->created_at->format('M j, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $member) }}" class="admin-inline-link">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-empty-cell">No members match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="admin-pagination">
                @if ($users->onFirstPage())
                    <span class="admin-pagination-disabled">Previous</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="admin-inline-link">Previous</a>
                @endif

                <span class="admin-pagination-info">Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>

                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="admin-inline-link">Next</a>
                @else
                    <span class="admin-pagination-disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Admin Dashboard | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <p class="home-welcome">Site administration</p>
        <h1 class="home-title">Dashboard</h1>
        <p class="home-lead">Overview of catalog data, member accounts, and newsletter sign-ups.</p>
    </section>

    <section class="admin-stats" aria-label="Site statistics">
        <a class="admin-stat-card admin-stat-card-link" href="{{ route('admin.catalog.sets.index') }}">
            <p class="admin-stat-number">{{ number_format($stats['plates']) }}</p>
            <p class="admin-stat-label">Catalog plates</p>
        </a>
        <a class="admin-stat-card admin-stat-card-link" href="{{ route('admin.catalog.sets.index') }}">
            <p class="admin-stat-number">{{ number_format($stats['sets']) }}</p>
            <p class="admin-stat-label">Distinct sets</p>
        </a>
        <div class="admin-stat-card">
            <p class="admin-stat-number">{{ number_format($stats['users']) }}</p>
            <p class="admin-stat-label">Members</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-number">{{ number_format($stats['collection_items']) }}</p>
            <p class="admin-stat-label">Collection entries</p>
        </div>
        <div class="admin-stat-card">
            <p class="admin-stat-number">{{ number_format($stats['newsletter_subscribers']) }}</p>
            <p class="admin-stat-label">Newsletter subscribers</p>
        </div>
        <div class="admin-stat-card admin-stat-card-warn">
            <p class="admin-stat-number">{{ number_format($stats['blocked_users']) }}</p>
            <p class="admin-stat-label">Blocked members</p>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h2 class="admin-panel-title">Recent members</h2>
            <a href="{{ route('admin.users.index') }}" class="admin-inline-link">All members &rarr;</a>
        </div>

        @if ($recentUsers->isEmpty())
            <p class="admin-empty">No member accounts yet.</p>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Username</th>
                            <th scope="col">Email</th>
                            <th scope="col">Status</th>
                            <th scope="col">Joined</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentUsers as $member)
                            <tr>
                                <td>{{ $member->username }}</td>
                                <td>{{ $member->email }}</td>
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection

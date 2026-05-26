@extends('layouts.app')

@section('title', 'Admin — ' . $user->username . ' | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.users.index') }}">Members</a></li>
                <li aria-current="page">{{ $user->username }}</li>
            </ol>
        </nav>
        <h1 class="home-title">{{ $user->username }}</h1>
        <p class="home-lead">{{ $user->name }} · {{ $user->email }}</p>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title">Account details</h2>
        <dl class="admin-detail-list">
            <div>
                <dt>Member since</dt>
                <dd>{{ $user->created_at->format('F j, Y g:i A') }}</dd>
            </div>
            <div>
                <dt>Phone</dt>
                <dd>{{ $user->phone ?: '—' }}</dd>
            </div>
            <div>
                <dt>Address</dt>
                <dd>{{ $user->address ?: '—' }}</dd>
            </div>
            <div>
                <dt>Collection entries</dt>
                <dd>{{ number_format($user->collection_items_count) }}</dd>
            </div>
            <div>
                <dt>Sets with entries</dt>
                <dd>{{ number_format($setCount) }}</dd>
            </div>
            <div>
                <dt>Public sets</dt>
                <dd>{{ number_format($publicSetCount) }}</dd>
            </div>
            <div>
                <dt>Owned quantity</dt>
                <dd>{{ number_format($collectionStats->owned_qty ?? 0) }}</dd>
            </div>
            <div>
                <dt>Want list quantity</dt>
                <dd>{{ number_format($collectionStats->wanted_qty ?? 0) }}</dd>
            </div>
        </dl>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title">Access &amp; status</h2>

        <form class="admin-form" method="post" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <label class="admin-toggle">
                <input type="hidden" name="is_admin" value="0">
                <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $user->is_admin))>
                <span>Site administrator</span>
            </label>

            <label class="admin-toggle">
                <input type="hidden" name="is_blocked" value="0">
                <input type="checkbox" name="is_blocked" value="1" @checked(old('is_blocked', $user->is_blocked))>
                <span>Block sign-in (suspended)</span>
            </label>

            <label class="auth-field">
                <span class="auth-label">Block reason (optional, shown only to admins)</span>
                <textarea name="blocked_reason" rows="3" maxlength="2000">{{ old('blocked_reason', $user->blocked_reason) }}</textarea>
            </label>

            @if ($user->is_blocked && $user->blocked_at)
                <p class="admin-note">Blocked since {{ $user->blocked_at->format('F j, Y g:i A') }}.</p>
            @endif

            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save changes</button>
            </p>
        </form>
    </section>

    <section class="admin-panel admin-panel-danger">
        <h2 class="admin-panel-title">Delete member</h2>
        <p class="admin-danger-text">
            Permanently removes this account and all collection entries. This cannot be undone.
        </p>
        <form method="post"
              action="{{ route('admin.users.destroy', $user) }}"
              onsubmit="return confirm('Delete {{ $user->username }} and all collection data? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="admin-danger-btn">Delete account</button>
        </form>
    </section>
</div>
@endsection

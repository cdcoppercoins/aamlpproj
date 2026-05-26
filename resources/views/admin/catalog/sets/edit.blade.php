@extends('layouts.app')

@section('title', 'Admin — Edit Set ' . $setMeta->set_code . ' | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.catalog.sets.index') }}">Catalog sets</a></li>
                <li><a href="{{ route('admin.catalog.sets.show', $setMeta->set_code) }}">{{ $setMeta->set_name }}</a></li>
                <li aria-current="page">Edit set</li>
            </ol>
        </nav>
        <h1 class="home-title">Edit set</h1>
        <p class="home-lead">Updates apply to all {{ number_format($setMeta->plate_count) }} plates with set code <code>{{ $setMeta->set_code }}</code>.</p>
    </section>

    <section class="admin-panel">
        <form class="admin-form" method="post" action="{{ route('admin.catalog.sets.update', $setMeta->set_code) }}">
            @csrf
            @method('PUT')
            <div class="admin-form-grid">
                <label class="auth-field">
                    <span class="auth-label">Set code</span>
                    <input type="text" value="{{ $setMeta->set_code }}" disabled>
                </label>
                <label class="auth-field admin-form-grid-span-2">
                    <span class="auth-label">Set name</span>
                    <input type="text" name="set_name" value="{{ old('set_name', $setMeta->set_name) }}" maxlength="255" required>
                </label>
                <label class="auth-field">
                    <span class="auth-label">Company / issuer</span>
                    <input type="text" name="company" value="{{ old('company', $setMeta->company) }}" maxlength="128">
                </label>
                <label class="auth-field">
                    <span class="auth-label">Year</span>
                    <input type="number" name="year" value="{{ old('year', $setMeta->year) }}" min="1800" max="2100">
                </label>
            </div>
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save set details</button>
                <a href="{{ route('admin.catalog.sets.show', $setMeta->set_code) }}" class="admin-inline-link">Cancel</a>
            </p>
        </form>
    </section>

    <section class="admin-panel admin-panel-danger">
        <h2 class="admin-panel-title">Delete entire set</h2>
        <p class="admin-danger-text">
            Removes every catalog plate in this set and deletes the image folder. Member collection rows for those plates are also removed.
        </p>
        <form method="post"
              action="{{ route('admin.catalog.sets.destroy', $setMeta->set_code) }}"
              onsubmit="return confirm('Delete set {{ $setMeta->set_code }} and ALL plates in it? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="admin-danger-btn">Delete set</button>
        </form>
    </section>
</div>
@endsection

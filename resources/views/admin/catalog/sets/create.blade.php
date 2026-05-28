@extends('layouts.app')

@section('title', 'Admin — Add Catalog Set | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.catalog.sets.index') }}">Catalog sets</a></li>
                <li aria-current="page">Add set</li>
            </ol>
        </nav>
        <h1 class="home-title">Add catalog set</h1>
        <p class="home-lead">
            Create a set code and image folder, then add the first plate entry. The set code must match the folder under
            <code>public/plates/</code> used by the gallery.
        </p>
    </section>

    <section class="admin-panel">
        <form class="admin-form" method="post" action="{{ route('admin.catalog.sets.store') }}">
            @csrf
            <div class="admin-form-grid">
                <label class="auth-field">
                    <span class="auth-label">Set code</span>
                    <input type="text"
                           name="set_code"
                           value="{{ old('set_code') }}"
                           maxlength="64"
                           pattern="[A-Za-z0-9._-]+"
                           required
                           autofocus
                           placeholder="e.g. m88p">
                    <span class="auth-hint">Letters, numbers, dots, dashes, underscores only.</span>
                </label>
                <label class="auth-field admin-form-grid-span-2">
                    <span class="auth-label">Set name</span>
                    <input type="text" name="set_name" value="{{ old('set_name') }}" maxlength="255" required>
                </label>
                <label class="auth-field">
                    <span class="auth-label">Company / issuer</span>
                    <input type="text" name="company" value="{{ old('company') }}" maxlength="128">
                </label>
                <label class="auth-field">
                    <span class="auth-label">Year</span>
                    <input type="number" name="year" value="{{ old('year') }}" min="1800" max="2100">
                </label>
            </div>
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Create set &amp; add first plate</button>
            </p>
        </form>
        <p class="admin-note" style="margin-top: 20px;">
            Adding many plates at once?
            <a href="{{ route('admin.catalog.import.create') }}">Bulk add set from CSV</a>
            — download a template, fill it in Excel or Google Sheets, then upload.
        </p>
    </section>
</div>
@endsection

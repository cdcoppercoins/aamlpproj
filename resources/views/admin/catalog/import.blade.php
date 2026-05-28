@extends('layouts.app')

@section('title', 'Admin — Bulk Add Set (CSV) | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.catalog.sets.index') }}">Catalog sets</a></li>
                <li aria-current="page">Bulk add set (CSV)</li>
            </ol>
        </nav>
        <h1 class="home-title">Bulk add set from CSV</h1>
        <p class="home-lead">
            Download the template, fill in one row per catalog plate (same <code>set_code</code> and <code>set_name</code> on every row), then upload the file here.
            Plate photos are added separately — not in the CSV.
        </p>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title">Step 1 — Download template</h2>
        <p class="admin-note">
            Optional: enter your new set details below so the downloaded file is pre-filled. Delete the three example rows after you understand the format, or edit them.
        </p>

        <div class="admin-catalog-import-form-wrap">
            <form class="admin-form admin-catalog-import-form" method="get" action="{{ route('admin.catalog.import.template') }}">
                <div class="admin-catalog-import-fields">
                    <div class="admin-catalog-import-row">
                        <label class="auth-field">
                            <span class="auth-label">Set code (for template)</span>
                            <input type="text"
                                   class="admin-input-ch-7"
                                   name="set_code"
                                   value="{{ old('set_code', $prefillSetCode) }}"
                                   maxlength="64"
                                   pattern="[A-Za-z0-9._-]+"
                                   placeholder="e.g. m88p">
                        </label>
                        <label class="auth-field">
                            <span class="auth-label">Set name (for template)</span>
                            <input type="text"
                                   class="admin-input-ch-35"
                                   name="set_name"
                                   value="{{ old('set_name', $prefillSetName) }}"
                                   maxlength="255"
                                   placeholder="e.g. 1953 Wheaties License Plates">
                        </label>
                    </div>
                    <div class="admin-catalog-import-row">
                        <label class="auth-field">
                            <span class="auth-label">Company / issuer</span>
                            <input type="text"
                                   class="admin-input-ch-45"
                                   name="company"
                                   value="{{ old('company', $prefillCompany) }}"
                                   maxlength="128">
                        </label>
                        <label class="auth-field">
                            <span class="auth-label">Year</span>
                            <input type="number"
                                   class="admin-input-ch-5"
                                   name="year"
                                   value="{{ old('year', $prefillYear) }}"
                                   min="1800"
                                   max="2100">
                        </label>
                    </div>
                </div>
                <p class="auth-actions">
                    <button type="submit" class="home-primary-btn">Download CSV template</button>
                </p>
            </form>
        </div>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title">Step 2 — Upload completed CSV</h2>
        <p class="admin-note">
            Required columns on every data row: <code>set_code</code>, <code>set_name</code>.
            Full column list: {{ implode(', ', $columnHeaders) }}.
        </p>

        <div class="admin-catalog-import-form-wrap">
            <form class="admin-form admin-catalog-import-form" method="post" action="{{ route('admin.catalog.import.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="admin-catalog-import-fields">
                    <label class="auth-field">
                        <span class="auth-label">Set code (optional check)</span>
                        <input type="text"
                               name="set_code"
                               value="{{ old('set_code', $prefillSetCode) }}"
                               maxlength="64"
                               pattern="[A-Za-z0-9._-]+"
                               placeholder="Leave blank to allow any set codes in file">
                        <span class="auth-hint">If filled, every row must use this set code. Creates <code>public/plates/{code}/</code> if missing.</span>
                    </label>
                    <label class="auth-field">
                        <span class="auth-label">CSV file</span>
                        <input type="file" name="csv_file" accept=".csv,text/csv" required>
                    </label>
                </div>
                <p class="auth-actions">
                    <button type="submit" class="home-primary-btn">Upload and import plates</button>
                    <a href="{{ route('admin.catalog.sets.index') }}" class="admin-inline-link">Cancel</a>
                </p>
            </form>
        </div>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title">Column reference</h2>
        <p class="admin-note">
            See <code>docs/PLATE_CSV_COLUMNS_REFERENCE.html</code> on your PC for definitions and more examples.
            <code>image_base</code> / <code>image_ext</code> can be blank for plates with no photo.
        </p>
    </section>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Admin — Import Catalog CSV | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.catalog.sets.index') }}">Catalog sets</a></li>
                <li aria-current="page">Import CSV</li>
            </ol>
        </nav>
        <h1 class="home-title">Import catalog CSV</h1>
        <p class="home-lead">
            Bulk-add or update catalog rows from a spreadsheet export. Images are not included in the CSV — upload those per plate afterward, or place files in
            <code>public/plates/{set_code}/</code> using matching <code>image_base</code> values.
        </p>
    </section>

    <section class="admin-panel">
        <p class="admin-note">
            Required CSV columns: <code>set_code</code>, <code>set_name</code>. See
            <code>docs/PLATE_CSV_COLUMNS_REFERENCE.html</code> in the project for all column definitions.
        </p>

        <form class="admin-form" method="post" action="{{ route('admin.catalog.import.store') }}" enctype="multipart/form-data">
            @csrf
            <label class="auth-field">
                <span class="auth-label">CSV file</span>
                <input type="file" name="csv_file" accept=".csv,text/csv" required>
            </label>
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Import plates</button>
                <a href="{{ route('admin.catalog.sets.index') }}" class="admin-inline-link">Cancel</a>
            </p>
        </form>
    </section>
</div>
@endsection

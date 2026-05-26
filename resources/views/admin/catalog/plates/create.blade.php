@extends('layouts.app')

@section('title', 'Admin — Add Plate | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.catalog.sets.index') }}">Catalog sets</a></li>
                <li><a href="{{ route('admin.catalog.sets.show', $setMeta->set_code) }}">{{ $setMeta->set_name }}</a></li>
                <li aria-current="page">Add plate</li>
            </ol>
        </nav>
        <h1 class="home-title">Add catalog plate</h1>
    </section>

    <section class="admin-panel">
        @include('components.admin-plate-form', [
            'plate' => $plate,
            'setMeta' => $setMeta,
            'jurisdictionTypes' => $jurisdictionTypes,
        ])
    </section>
</div>
@endsection

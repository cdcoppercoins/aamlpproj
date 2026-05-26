@extends('layouts.app')

@section('title', 'Admin — Edit Plate | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.catalog.sets.index') }}">Catalog sets</a></li>
                <li><a href="{{ route('admin.catalog.sets.show', $plate->set_code) }}">{{ $plate->set_name }}</a></li>
                <li aria-current="page">Edit plate</li>
            </ol>
        </nav>
        <h1 class="home-title">Edit catalog plate</h1>
        <p class="home-lead">
            {{ $plate->jurisdiction ?: 'No jurisdiction' }}
            @if ($plate->serial_number) · {{ $plate->serial_number }} @endif
            · ID {{ $plate->id }}
        </p>
    </section>

    <section class="admin-panel">
        @include('components.admin-plate-form', [
            'plate' => $plate,
            'setMeta' => $setMeta,
            'jurisdictionTypes' => $jurisdictionTypes,
        ])
    </section>

    <section class="admin-panel admin-panel-danger">
        <h2 class="admin-panel-title">Delete plate</h2>
        <form method="post"
              action="{{ route('admin.catalog.plates.destroy', $plate) }}"
              onsubmit="return confirm('Delete this catalog plate?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="admin-danger-btn">Delete plate</button>
        </form>
    </section>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Admin — Edit Home Banner | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.home-hero.index') }}">Home banners</a></li>
                <li aria-current="page">Edit slide</li>
            </ol>
        </nav>
        <h1 class="home-title">Edit home banner slide</h1>
        <p class="home-lead">{{ $slide->headline }}</p>
    </section>

    <section class="admin-panel">
        <form class="admin-form admin-catalog-form" method="post" action="{{ route('admin.home-hero.update', $slide) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('components.admin-hero-slide-form', [
                'slide' => $slide,
                'linkOptionGroups' => $linkOptionGroups,
                'requireImage' => false,
                'inputId' => 'hero-image-edit',
            ])
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save slide</button>
                <a href="{{ route('admin.home-hero.index') }}" class="admin-inline-link">Cancel</a>
            </p>
        </form>
    </section>

    <section class="admin-panel admin-panel-danger">
        <h2 class="admin-panel-title">Delete slide</h2>
        <form method="post"
              action="{{ route('admin.home-hero.destroy', $slide) }}"
              onsubmit="return confirm('Delete this home banner slide?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="home-secondary-btn admin-danger-btn">Delete slide</button>
        </form>
    </section>
</div>
@endsection

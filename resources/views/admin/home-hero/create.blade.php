@extends('layouts.app')

@section('title', 'Admin — Add Home Banner | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.home-hero.index') }}">Home banners</a></li>
                <li aria-current="page">Add slide</li>
            </ol>
        </nav>
        <h1 class="home-title">Add home banner slide</h1>
    </section>

    <section class="admin-panel">
        <form class="admin-form admin-catalog-form" method="post" action="{{ route('admin.home-hero.store') }}" enctype="multipart/form-data">
            @csrf
            @include('components.admin-hero-slide-form', [
                'slide' => $slide,
                'linkOptionGroups' => $linkOptionGroups,
                'requireImage' => true,
                'inputId' => 'hero-image-create',
            ])
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Add slide</button>
                <a href="{{ route('admin.home-hero.index') }}" class="admin-inline-link">Cancel</a>
            </p>
        </form>
    </section>
</div>
@endsection

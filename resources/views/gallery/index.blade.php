@extends('layouts.app')

@section('title', 'Mini License Plate Sets Gallery | MiniLicensePlates.com')

@section('meta_description', 'Browse miniature license plate sets by year and issuer. View sample plates from Post, Topps, General Mills, Goudey, and other sets with catalog reference numbers.')

@section('canonical_url', route('gallery'))

@section('content')
<div class="home-page gallery-catalog-page">
    <section class="home-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">Gallery</li>
            </ol>
        </nav>
        <p class="home-welcome">Browse set images</p>
        <h1 class="home-title">Gallery</h1>
        <p class="home-lead">All sets listed by year and set code. Select a set to view its plate images.</p>
    </section>

    <div class="gallery-catalog-grid">
        @foreach ($sets as $set)
            <a class="gallery-catalog-set" href="{{ route('gallery.show', $set->set_name) }}">
                <span class="gallery-catalog-set-image-wrap">
                    <img class="gallery-catalog-set-image"
                         src="{{ $set->sample_image_url }}"
                         alt="{{ $set->set_name }} miniature license plate sample"
                         loading="lazy"
                         onerror="this.onerror=null;this.src='{{ asset('plate_missing.png') }}';">
                </span>
                <span class="gallery-catalog-set-labels">
                    <span class="gallery-catalog-set-name">{{ $set->set_name }}</span>
                    <span class="gallery-catalog-set-meta">
                        <span class="gallery-catalog-set-code">set code: {{ strtolower($set->set_code) }}</span>
                        <span class="gallery-catalog-set-cat-ref">ref: {{ ! empty($set->cat_ref) ? $set->cat_ref : 'unknown' }}</span>
                    </span>
                    <span class="gallery-catalog-set-company">{{ $set->company ?? '' }}</span>
                </span>
            </a>
        @endforeach
    </div>
</div>
@endsection

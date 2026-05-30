@extends('layouts.app')

@section('title', 'Admin — Edit Site Link | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.site-links.index') }}">Site links</a></li>
                <li aria-current="page">Edit</li>
            </ol>
        </nav>
        <h1 class="home-title">Edit site link</h1>
    </section>

    <section class="admin-panel">
        @if ($errors->any())
            <ul class="admin-flash-errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form class="admin-form admin-catalog-form admin-site-link-form" method="post" action="{{ route('admin.site-links.update', $link) }}">
            @csrf
            @method('PUT')
            @include('components.admin-site-link-form', [
                'link' => $link,
                'pages' => $pages,
                'placements' => $placements,
            ])
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save changes</button>
                @if ($link->href())
                    <a href="{{ $link->href() }}" class="admin-inline-link" target="_blank" rel="noopener">View live</a>
                @endif
                <a href="{{ route('admin.site-links.index') }}" class="admin-inline-link">Cancel</a>
            </p>
        </form>
    </section>
</div>
@endsection

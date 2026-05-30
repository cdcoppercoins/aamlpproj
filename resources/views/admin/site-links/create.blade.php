@extends('layouts.app')

@section('title', 'Admin — Add Site Link | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.site-links.index') }}">Site links</a></li>
                <li aria-current="page">Add link</li>
            </ol>
        </nav>
        <h1 class="home-title">Add site link</h1>
    </section>

    <section class="admin-panel">
        @if ($errors->any())
            <ul class="admin-flash-errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        @if ($pages->isEmpty())
            <p class="admin-flash-errors">No static pages yet. You can still add links that use a custom URL such as <code>/contribute</code>.</p>
        @endif

        <form class="admin-form admin-catalog-form admin-site-link-form" method="post" action="{{ route('admin.site-links.store') }}">
            @csrf
            @include('components.admin-site-link-form', [
                'link' => $link,
                'pages' => $pages,
                'placements' => $placements,
            ])
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save link</button>
                <a href="{{ route('admin.site-links.index') }}" class="admin-inline-link">Cancel</a>
            </p>
        </form>
    </section>
</div>
@endsection

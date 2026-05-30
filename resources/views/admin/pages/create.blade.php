@extends('layouts.app')

@section('title', 'Admin — Add Static Page | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.pages.index') }}">Static pages</a></li>
                <li aria-current="page">Add page</li>
            </ol>
        </nav>
        <h1 class="home-title">Add static page</h1>
    </section>

    <section class="admin-panel">
        @if ($errors->any())
            <ul class="admin-flash-errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form class="admin-form admin-catalog-form" method="post" action="{{ route('admin.pages.store') }}">
            @csrf
            @include('components.admin-page-form', ['page' => $page])
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save page</button>
                <a href="{{ route('admin.pages.index') }}" class="admin-inline-link">Cancel</a>
            </p>
        </form>
    </section>
</div>
@endsection

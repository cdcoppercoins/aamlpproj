@extends('layouts.app')

@section('title', 'Admin — Add History Entry | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.history-timeline.index') }}">History timeline</a></li>
                <li aria-current="page">Add entry</li>
            </ol>
        </nav>
        <h1 class="home-title">Add timeline entry</h1>
    </section>

    <section class="admin-panel">
        <form class="admin-form admin-catalog-form" method="post" action="{{ route('admin.history-timeline.store') }}" enctype="multipart/form-data">
            @csrf
            @include('components.admin-history-entry-form', ['entry' => $entry])
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save entry</button>
                <a href="{{ route('admin.history-timeline.index') }}" class="admin-inline-link">Cancel</a>
            </p>
        </form>
    </section>
</div>
@endsection

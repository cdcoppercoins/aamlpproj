@extends('layouts.app')

@section('title', 'Admin — Edit History Entry | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.history-timeline.index') }}">History timeline</a></li>
                <li aria-current="page">Edit entry</li>
            </ol>
        </nav>
        <h1 class="home-title">Edit timeline entry</h1>
        <p class="home-lead">{{ $entry->label }}</p>
    </section>

    <section class="admin-panel">
        <form class="admin-form admin-catalog-form" method="post" action="{{ route('admin.history-timeline.update', $entry) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('components.admin-history-entry-form', ['entry' => $entry])
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save entry</button>
                <a href="{{ route('admin.history-timeline.index') }}" class="admin-inline-link">Cancel</a>
            </p>
        </form>
    </section>

    <section class="admin-panel admin-panel-danger">
        <h2 class="admin-panel-title">Delete entry</h2>
        <form method="post"
              action="{{ route('admin.history-timeline.destroy', $entry) }}"
              onsubmit="return confirm('Delete this timeline entry?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="home-secondary-btn admin-danger-btn">Delete entry</button>
        </form>
    </section>
</div>
@endsection

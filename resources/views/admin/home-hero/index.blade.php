@extends('layouts.app')

@section('title', 'Admin — Home Banners | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <h1 class="home-title">Home banners</h1>
        <p class="home-lead">Manage the rotating slides on the home page — images, headlines, and button links.</p>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel-title">Rotator timing</h2>
        <form class="admin-form admin-form-inline" method="post" action="{{ route('admin.home-hero.settings.update') }}">
            @csrf
            @method('PUT')
            <label class="auth-field">
                <span class="auth-label">Seconds per slide</span>
                <input type="number" name="interval_seconds" value="{{ old('interval_seconds', (int) round($settings->interval_ms / 1000)) }}" min="2" max="60" required>
            </label>
            <p class="auth-actions">
                <button type="submit" class="home-secondary-btn">Save timing</button>
            </p>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h2 class="admin-panel-title">Slides</h2>
            <a href="{{ route('admin.home-hero.create') }}" class="home-primary-btn">Add slide</a>
        </div>

        @if ($slides->isEmpty())
            <p>No slides yet. <a href="{{ route('admin.home-hero.create') }}">Add the first slide</a>.</p>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Preview</th>
                            <th scope="col">Headline</th>
                            <th scope="col">Link</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($slides as $slide)
                            <tr>
                                <td>{{ $slide->sort_order }}</td>
                                <td>
                                    <img src="{{ asset($slide->image) }}" alt="" class="admin-hero-thumb" width="115" height="28">
                                </td>
                                <td>
                                    <strong>{{ $slide->headline }}</strong>
                                    @if ($slide->subline)
                                        <br><span class="admin-table-muted">{{ $slide->subline }}</span>
                                    @endif
                                </td>
                                <td>{{ \App\Support\HeroLinkOptions::labelForSlide($slide) }}</td>
                                <td>
                                    @if ($slide->is_active)
                                        <span class="admin-badge admin-badge-active">Active</span>
                                    @else
                                        <span class="admin-badge">Hidden</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.home-hero.edit', $slide) }}" class="admin-inline-link">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection

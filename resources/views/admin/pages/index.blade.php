@extends('layouts.app')

@section('title', 'Admin — Static Pages | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <h1 class="home-title">Static pages</h1>
        <p class="home-lead">Create simple site pages (About, policies, etc.) with drag-and-drop images and text that wraps around them.</p>
    </section>

    @if (session('success'))
        <p class="admin-flash-success" role="status">{{ session('success') }}</p>
    @endif

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h2 class="admin-panel-title">All static pages</h2>
            <a href="{{ route('admin.pages.create') }}" class="home-primary-btn">Add page</a>
        </div>

        @if ($pages->isEmpty())
            <p class="admin-empty">No static pages yet. <a href="{{ route('admin.pages.create') }}">Add the first page</a>.</p>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">URL</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pages as $page)
                            <tr>
                                <td><strong>{{ $page->title }}</strong></td>
                                <td>
                                    <code>/{{ $page->slug }}</code>
                                    @if ($page->is_published)
                                        <br><a href="{{ $page->publicUrl() }}" class="admin-inline-link" target="_blank" rel="noopener">View live</a>
                                    @endif
                                </td>
                                <td>
                                    @if ($page->is_published)
                                        <span class="admin-badge admin-badge-active">Published</span>
                                    @else
                                        <span class="admin-badge">Draft</span>
                                    @endif
                                </td>
                                <td class="admin-table-actions">
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="admin-inline-link">Edit</a>
                                    <span class="admin-action-sep" aria-hidden="true">·</span>
                                    <form method="post"
                                          action="{{ route('admin.pages.destroy', $page) }}"
                                          class="admin-inline-form"
                                          onsubmit="return confirm('Delete this page? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-inline-danger-btn">Delete</button>
                                    </form>
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

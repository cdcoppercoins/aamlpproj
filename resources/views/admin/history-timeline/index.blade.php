@extends('layouts.app')

@section('title', 'Admin — History Timeline | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <h1 class="home-title">History timeline</h1>
        <p class="home-lead">Edit the accordion timeline on the <a href="{{ route('history') }}">History</a> page — labels, stories, photos, and order.</p>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h2 class="admin-panel-title">Timeline entries</h2>
            <a href="{{ route('admin.history-timeline.create') }}" class="home-primary-btn">Add entry</a>
        </div>

        @if ($entries->isEmpty())
            <p class="admin-empty">No entries yet. <a href="{{ route('admin.history-timeline.create') }}">Add the first entry</a>.</p>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Thumb</th>
                            <th scope="col">Label</th>
                            <th scope="col">Headline</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                            <tr>
                                <td>{{ $entry->sort_order }}</td>
                                <td>
                                    @if ($entry->imagePath())
                                        <img src="{{ asset($entry->imagePath()) }}" alt="" class="admin-history-thumb" width="44" height="44">
                                    @else
                                        <span class="admin-table-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $entry->label }}</td>
                                <td>
                                    <strong>{{ $entry->title }}</strong>
                                    <br><span class="admin-table-muted">{{ $entry->slug }}</span>
                                </td>
                                <td>
                                    @if ($entry->is_published)
                                        <span class="admin-badge admin-badge-active">Published</span>
                                    @else
                                        <span class="admin-badge">Draft</span>
                                    @endif
                                </td>
                                <td class="admin-table-actions">
                                    <a href="{{ route('admin.history-timeline.edit', $entry) }}" class="admin-inline-link">Edit</a>
                                    <span class="admin-action-sep" aria-hidden="true">·</span>
                                    <form method="post"
                                          action="{{ route('admin.history-timeline.destroy', $entry) }}"
                                          class="admin-inline-form"
                                          onsubmit="return confirm('Delete this timeline entry? This cannot be undone.');">
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

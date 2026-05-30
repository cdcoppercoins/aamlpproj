@extends('layouts.app')

@section('title', 'Admin — Site Links | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <h1 class="home-title">Site links</h1>
        <p class="home-lead">Connect menu and footer text links to static pages, site paths, or external URLs.</p>
    </section>

    @if (session('success'))
        <p class="admin-flash-success" role="status">{{ session('success') }}</p>
    @endif

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h2 class="admin-panel-title">All site links</h2>
            <a href="{{ route('admin.site-links.create') }}" class="home-primary-btn">Add link</a>
        </div>

        @if ($linksByPlacement->isEmpty())
            <p class="admin-empty">No site links yet. <a href="{{ route('admin.site-links.create') }}">Add the first link</a>.</p>
        @else
            @foreach ($placements as $placementKey => $placementLabel)
                @php $links = $linksByPlacement->get($placementKey, collect()); @endphp
                @if ($links->isNotEmpty())
                    <h3 class="admin-subsection-title">{{ $placementLabel }}</h3>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th scope="col">Link text</th>
                                    <th scope="col">Destination</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($links as $link)
                                    <tr>
                                        <td><strong>{{ $link->label }}</strong></td>
                                        <td>
                                            @if ($link->destinationLabel() !== '')
                                                <code>{{ $link->destinationLabel() }}</code>
                                                @if ($link->href())
                                                    <br><a href="{{ $link->href() }}" class="admin-inline-link" target="_blank" rel="noopener">View live</a>
                                                @endif
                                            @else
                                                <span class="admin-table-muted">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($link->is_published && $link->href())
                                                <span class="admin-badge admin-badge-active">Live</span>
                                            @elseif ($link->is_published && $link->page_id)
                                                <span class="admin-badge">Waiting on page</span>
                                            @elseif ($link->is_published)
                                                <span class="admin-badge">Invalid URL</span>
                                            @else
                                                <span class="admin-badge">Hidden</span>
                                            @endif

                                        </td>
                                        <td class="admin-table-actions">
                                            <a href="{{ route('admin.site-links.edit', $link) }}" class="admin-inline-link">Edit</a>
                                            <span class="admin-action-sep" aria-hidden="true">·</span>
                                            <form method="post"
                                                  action="{{ route('admin.site-links.destroy', $link) }}"
                                                  class="admin-inline-form"
                                                  onsubmit="return confirm('Delete this site link?');">
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
            @endforeach
        @endif
    </section>
</div>
@endsection

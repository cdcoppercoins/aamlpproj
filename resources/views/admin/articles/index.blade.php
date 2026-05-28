@extends('layouts.app')

@section('title', 'Admin — Articles | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <h1 class="home-title">Articles</h1>
        <p class="home-lead">Manage articles on the <a href="{{ route('articles.index') }}">Articles</a> page — author, title, images, and body.</p>
    </section>

    @if (session('success'))
        <p class="admin-flash-success" role="status">{{ session('success') }}</p>
    @endif

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h2 class="admin-panel-title">All articles</h2>
            <a href="{{ route('admin.articles.create') }}" class="home-primary-btn">Add article</a>
        </div>

        @if ($articles->isEmpty())
            <p class="admin-empty">No articles yet. <a href="{{ route('admin.articles.create') }}">Add the first article</a>.</p>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Featured</th>
                            <th scope="col">Title</th>
                            <th scope="col">Author</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($articles as $article)
                            <tr>
                                <td>
                                    @if ($article->heroImagePath())
                                        <img src="{{ asset($article->heroImagePath()) }}" alt="" class="admin-history-thumb" width="44" height="44">
                                    @else
                                        <span class="admin-table-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $article->title }}</strong>
                                    @if ($article->subtitle)
                                        <br><span class="admin-table-muted">{{ $article->subtitle }}</span>
                                    @endif
                                    <br><span class="admin-table-muted">{{ $article->slug }}</span>
                                </td>
                                <td>{{ $article->author }}</td>
                                <td>
                                    @if ($article->is_published)
                                        <span class="admin-badge admin-badge-active">Published</span>
                                        @if ($article->published_at)
                                            <br><span class="admin-table-muted">{{ $article->published_at->format('M j, Y') }}</span>
                                        @endif
                                    @else
                                        <span class="admin-badge">Draft</span>
                                    @endif
                                </td>
                                <td class="admin-table-actions">
                                    @if (! $article->is_published)
                                        <form method="post"
                                              action="{{ route('admin.articles.publish', $article) }}"
                                              class="admin-inline-form">
                                            @csrf
                                            <button type="submit" class="admin-inline-link admin-publish-btn">Publish</button>
                                        </form>
                                        <span class="admin-action-sep" aria-hidden="true">·</span>
                                    @endif
                                    <a href="{{ route('admin.articles.edit', $article) }}" class="admin-inline-link">Edit</a>
                                    <span class="admin-action-sep" aria-hidden="true">·</span>
                                    <form method="post"
                                          action="{{ route('admin.articles.destroy', $article) }}"
                                          class="admin-inline-form"
                                          onsubmit="return confirm('Delete this article? This cannot be undone.');">
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

@extends('layouts.app')

@section('title', ($query ?? '') !== '' ? 'Article search | MiniLicensePlates.com' : 'Articles | MiniLicensePlates.com')

@section('meta_description', 'Articles about miniature license plate collecting — research, history, and commentary from MiniLicensePlates.com.')

@section('canonical_url', route('articles.index'))

@if (($query ?? '') !== '')
@section('robots', 'noindex, follow')
@endif

@section('content')
<div class="home-page articles-page">
    <section class="articles-hero">
        <h1 class="home-title">Articles</h1>
        <p class="home-lead">Research notes, history, and commentary on miniature license plate collectibles.</p>
    </section>

    <section class="articles-search" aria-label="Search articles">
        <form class="articles-search-form" method="get" action="{{ route('articles.index') }}">
            <label class="articles-search-label" for="articles-q">Search by keyword</label>
            <div class="articles-search-row">
                <input type="search"
                       id="articles-q"
                       name="q"
                       value="{{ $query }}"
                       class="articles-search-input"
                       placeholder="Title, author, or words in the article"
                       maxlength="200"
                       autocomplete="off">
                <button type="submit" class="home-primary-btn articles-search-btn">Search</button>
            </div>
            @if ($query !== '')
                <p class="articles-search-meta">
                    <a href="{{ route('articles.index') }}" class="admin-inline-link">Show all articles</a>
                </p>
            @endif
        </form>
    </section>

    @if ($query !== '' && $articles->isEmpty())
        <p class="articles-empty">No articles match <strong>{{ $query }}</strong>. Try fewer or different words.</p>
    @elseif ($articles->isEmpty())
        <p class="articles-empty">No articles published yet. Please check back soon.</p>
    @else
        @if ($query !== '')
            <p class="articles-results-count" role="status">
                {{ $articles->count() }} {{ $articles->count() === 1 ? 'result' : 'results' }} for <strong>{{ $query }}</strong>
            </p>
        @endif
        @include('articles._list', ['articles' => $articles])
    @endif
</div>
@endsection

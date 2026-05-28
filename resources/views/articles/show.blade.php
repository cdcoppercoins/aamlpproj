@extends('layouts.app')

@section('title', $article->title . ' | Articles | MiniLicensePlates.com')

@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($article->subtitle ?: $article->body), 155))

@section('canonical_url', route('articles.show', $article->slug))

@section('content')
<div class="home-page articles-page articles-show-page">
    <nav class="gallery-breadcrumbs articles-breadcrumbs" aria-label="Breadcrumb">
        <ol class="gallery-breadcrumbs-list">
            <li><a href="{{ route('articles.index') }}">Articles</a></li>
            <li aria-current="page">{{ $article->title }}</li>
        </ol>
    </nav>

    <article class="articles-article">
        <header class="articles-article-header">
            @if ($article->displayDate())
                <p class="articles-article-date">{{ $article->displayDate() }}</p>
            @endif
            <h1 class="articles-article-title">{{ $article->title }}</h1>
            @if ($article->subtitle)
                <p class="articles-article-subtitle">{{ $article->subtitle }}</p>
            @endif
            <p class="articles-article-author">By {{ $article->author }}</p>
        </header>

        @if ($article->heroImageUrl())
            <figure class="articles-article-hero">
                <img src="{{ $article->heroImageUrl() }}"
                     alt="{{ $article->hero_image_alt ?: $article->title }}"
                     class="articles-article-hero-img">
            </figure>
        @endif

        <div class="articles-article-body">
            {!! $article->body !!}
        </div>

        @if ($article->images->isNotEmpty())
            <section class="articles-gallery" aria-label="Article images">
                <h2 class="articles-gallery-heading">Images</h2>
                <ul class="articles-gallery-list">
                    @foreach ($article->images as $image)
                        <li class="articles-gallery-item">
                            <figure>
                                <img src="{{ $image->imageUrl() }}"
                                     alt="{{ $image->alt ?: $article->title }}"
                                     class="articles-gallery-img"
                                     loading="lazy">
                                @if ($image->caption)
                                    <figcaption class="articles-gallery-caption">{{ $image->caption }}</figcaption>
                                @endif
                            </figure>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </article>

    <p class="articles-back">
        <a href="{{ route('articles.index') }}" class="home-secondary-btn">&larr; All articles</a>
    </p>
</div>
@endsection

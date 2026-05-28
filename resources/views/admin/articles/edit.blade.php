@extends('layouts.app')

@section('title', 'Admin — Edit Article | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.articles.index') }}">Articles</a></li>
                <li aria-current="page">Edit</li>
            </ol>
        </nav>
        <h1 class="home-title">Edit article</h1>
        <p class="home-lead">{{ $article->title }}</p>
    </section>

    <section class="admin-panel">
        @if ($errors->any())
            <ul class="admin-flash-errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form class="admin-form admin-catalog-form" method="post" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('components.admin-article-form', ['article' => $article])
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save changes</button>
                <a href="{{ route('admin.articles.index') }}" class="admin-inline-link">Cancel</a>
                @if ($article->is_published)
                    <a href="{{ route('articles.show', $article->slug) }}" class="admin-inline-link" target="_blank" rel="noopener">View on site</a>
                @endif
            </p>
        </form>
    </section>
</div>
@endsection

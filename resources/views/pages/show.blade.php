@extends('layouts.app')

@section('title', $page->title . ' | MiniLicensePlates.com')

@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->body), 155))

@section('canonical_url', $page->publicUrl())

@section('content')
<div class="home-page static-page">
    <article class="static-page-article">
        <h1 class="static-page-title">{{ $page->title }}</h1>
        <div class="static-page-body">
            {!! $page->body !!}
        </div>
    </article>
</div>
@endsection

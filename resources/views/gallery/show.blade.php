@extends('layouts.app')

@section('title', $selectedSet . ' Mini License Plates | MiniLicensePlates.com')

@section('meta_description', $metaDescription)

@section('canonical_url', route('gallery.show', $selectedSet))

@section('og_type', 'article')

@section('og_image', $sampleImage)

@section('content')
<div class="home-page gallery-set-page">
    <div class="gallery-set-toolbar">
        <h1 class="gallery-set-title">{{ $selectedSet }}</h1>
        <a class="gallery-set-back" href="{{ route('gallery') }}">Return to Gallery</a>
    </div>

    @if (!empty($images))
        <div class="gallery-set-poster" aria-label="{{ $selectedSet }} plate images">
            @foreach ($images as $pair)
                <div class="gallery-set-cell">
                    @if ($pair['b'])
                        <img class="gallery-set-poster-img thumb-img"
                             src="{{ $pair['a'] }}"
                             data-hover="{{ $pair['b'] }}"
                             data-original="{{ $pair['a'] }}"
                             onmouseover="if(this.dataset.hover){this.src=this.dataset.hover}"
                             onmouseout="this.src=this.dataset.original"
                             alt="{{ $pair['jurisdiction'] ?? $selectedSet }} miniature license plate">
                    @else
                        <img class="gallery-set-poster-img thumb-img"
                             src="{{ $pair['a'] }}"
                             data-original="{{ $pair['a'] }}"
                             alt="{{ $pair['jurisdiction'] ?? $selectedSet }} miniature license plate">
                    @endif
                    @if (! empty($pair['caption']))
                        <p class="gallery-set-jurisdiction">{{ $pair['caption'] }}</p>
                    @elseif (! empty($pair['jurisdiction']))
                        <p class="gallery-set-jurisdiction">{{ $pair['jurisdiction'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="gallery-set-empty">No images found for {{ $selectedSet }}.</p>
    @endif
</div>

<div id="imageModal" class="modal">
    <span class="modal-close">&times;</span>
    <img id="modalImg" src="" alt="">
</div>

@include('components.modal_script')
@endsection

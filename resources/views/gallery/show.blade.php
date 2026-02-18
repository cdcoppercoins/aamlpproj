@extends('layouts.app')

@section('title', $selectedSet . ' | Gallery | MiniLicensePlates.com')

@section('content')
@if($hasInfo)
    @include('setinfo.' . $folder . '_info', ['selectedSet' => $selectedSet])
@else
    <div class="set-width"><a class="home-box" href="{{ route('gallery') }}">Gallery Home</a></div>
@endif

@if(!empty($images))
    <div class="image-container set-width">
        @foreach($images as $pair)
            @if($pair['b'])
                <img class="thumb-img"
                     src="{{ $pair['a'] }}"
                     data-hover="{{ $pair['b'] }}"
                     data-original="{{ $pair['a'] }}"
                     onmouseover="this.src=this.dataset.hover"
                     onmouseout="this.src=this.dataset.original" alt="">
            @else
                <img class="thumb-img"
                     src="{{ $pair['a'] }}"
                     data-original="{{ $pair['a'] }}"
                     alt="">
            @endif
        @endforeach
    </div>

    @if($hasVarieties)
        @include('setinfo.' . $folder . '_varieties')
    @endif
@else
    <p>No images found for {{ $selectedSet }}.</p>
@endif

<!-- Modal -->
<div id="imageModal" class="modal">
    <span class="modal-close">&times;</span>
    <img id="modalImg" src="" alt="">
</div>

@include('components.modal_script')
@endsection

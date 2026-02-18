@extends('layouts.app')

@section('title', 'Gallery | MiniLicensePlates.com')

@section('content')
<div class="set-list set-width">
    @foreach($folderMap as $setName => $folderCode)
        @php $enabled = !empty($availableSets[$setName]); @endphp
        <a
            class="set-box{{ $enabled ? '' : ' disabled' }}"
            @if($enabled)
                href="{{ route('gallery.show', ['setName' => urlencode($setName)]) }}"
            @else
                href="javascript:void(0)"
            @endif
        >
            @if($enabled && !empty($setThumbnails[$setName]))
                <img
                    src="{{ $setThumbnails[$setName] }}"
                    alt="{{ htmlspecialchars($setName, ENT_QUOTES, 'UTF-8') }} thumbnail"
                    class="set-thumb">
            @else
                <div class="set-thumb placeholder"></div>
            @endif
            <span class="set-label">{{ $setName }}</span>
        </a>
    @endforeach
</div>
@endsection

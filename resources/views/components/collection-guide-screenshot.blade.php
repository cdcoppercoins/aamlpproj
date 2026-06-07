@props([
    'file',
    'alt',
    'caption' => null,
    'wide' => false,
])

@php
    $url = asset('images/collection-guide/' . $file);
    $path = public_path('images/collection-guide/' . $file);
    $exists = is_file($path);
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $inlineSvg = $exists && $extension === 'svg' ? file_get_contents($path) : null;
@endphp

<figure class="collection-guide-figure{{ $wide ? ' collection-guide-figure--wide' : '' }}">
    @if ($inlineSvg !== null && $inlineSvg !== false)
        <div class="collection-guide-svg" role="img" aria-label="{{ $alt }}">
            {!! $inlineSvg !!}
        </div>
    @elseif ($exists)
        <img src="{{ $url }}"
             alt="{{ $alt }}"
             class="collection-guide-img"
             loading="eager"
             decoding="async">
    @else
        <p class="collection-guide-img-missing" role="status">
            Illustration missing. Add <code>public/images/collection-guide/{{ $file }}</code> on the server (or deploy that folder).
        </p>
    @endif
    @if ($caption)
        <figcaption class="collection-guide-caption">{{ $caption }}</figcaption>
    @endif
</figure>

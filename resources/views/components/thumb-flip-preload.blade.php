@foreach (array_unique($urls ?? []) as $url)
    @if ($url)
        <link rel="preload" as="image" href="{{ $url }}">
    @endif
@endforeach

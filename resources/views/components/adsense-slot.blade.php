@php
    $placement = $placement ?? '';
    $slotId = \App\Support\AdSense::slotId($placement);
    $render = \App\Support\AdSense::shouldRenderSlot($placement);
    $isLive = \App\Support\AdSense::shouldShowAds() && $slotId !== '';
    $label = \App\Support\AdSense::placementLabel($placement);
@endphp

@if ($render)
    <div class="ad-slot ad-slot--{{ $placement }}" data-ad-placement="{{ $placement }}">
        @if ($isLive)
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="{{ \App\Support\AdSense::clientId() }}"
                 data-ad-slot="{{ $slotId }}"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
            <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
        @else
            <p class="ad-slot-placeholder" aria-hidden="true">Ad placement: {{ $label }}</p>
        @endif
    </div>
@endif

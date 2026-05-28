@if (\App\Support\AdSense::enabled() && \App\Support\AdSense::clientId() !== '')
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ \App\Support\AdSense::clientId() }}"
        crossorigin="anonymous"></script>
@endif

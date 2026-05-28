<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MiniLicensePlates.com')</title>
    @include('components.seo-head')
    @include('components.adsense-head')
    <link rel="stylesheet" href="{{ asset('main.css') }}" />
</head>
<body>
@include('components.header')
<div class="content-wrapper">
@include('components.flash-messages')
@include('components.adsense-slot', ['placement' => 'below-header'])
@yield('content')
@include('components.footer')
</div>
@stack('scripts')
</body>
</html>

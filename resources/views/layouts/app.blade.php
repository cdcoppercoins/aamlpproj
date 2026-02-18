<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MiniLicensePlates.com')</title>
    <link rel="stylesheet" href="{{ asset('main.css') }}" />
</head>
<body>
@include('components.header')
<div class="content-wrapper">
@yield('content')
</div>
@include('components.footer')
</body>
</html>

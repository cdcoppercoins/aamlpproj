@php
    $defaultDescription = 'The largest visual reference and pricing catalog for miniature license plates from Post, Topps, General Mills, Goudey, and other candy, gum, and cereal premiums. Browse sets, search by year and jurisdiction, and view catalog values.';

    $seoTitle = trim($__env->yieldContent('title')) ?: 'MiniLicensePlates.com';
    $seoDescription = trim($__env->yieldContent('meta_description')) ?: $defaultDescription;
    $seoCanonical = trim($__env->yieldContent('canonical_url')) ?: url()->current();
    $seoRobots = trim($__env->yieldContent('robots')) ?: 'index, follow';
    $seoOgType = trim($__env->yieldContent('og_type')) ?: 'website';
    $seoOgImage = trim($__env->yieldContent('og_image')) ?: asset('home_top_banner.jpg');
@endphp

<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="{{ $seoRobots }}">
<link rel="canonical" href="{{ $seoCanonical }}">

<meta property="og:locale" content="en_US">
<meta property="og:site_name" content="MiniLicensePlates.com">
<meta property="og:type" content="{{ $seoOgType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:image" content="{{ $seoOgImage }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoOgImage }}">

@stack('structured_data')

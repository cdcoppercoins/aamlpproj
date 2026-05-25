<header class="site-header">
  <div class="header-frame">
    <div class="header-inner banner-split">
      <img src="{{ asset('header_banner_left.png') }}"
           alt="MiniLicensePlates.com — miniature license plate reference site"
           class="site-banner site-banner-left">
      <img src="{{ asset('header_banner_right.png') }}"
           alt=""
           class="site-banner site-banner-right"
           aria-hidden="true">
    </div>
  </div>

  <!-- NEW wrapper that is as wide as the content area and filled with white -->
  <div class="nav-outer">
    <nav class="main-nav">
      <ul>
        <li><a href="{{ route('home') }}">HOME</a></li>
        <li><a href="{{ route('gallery') }}">GALLERY</a></li>
        <li><a href="{{ route('search') }}">SEARCH</a></li>
        <li><a href="{{ route('about') }}">ABOUT</a></li>
        <li><a href="{{ route('history') }}">HISTORY</a></li>
        <li><a href="{{ route('contribute') }}">CONTRIBUTE</a></li>
        <li><a href="https://www.ebay.com/str/minilicenseplates" target="new">SHOP</a></li>
      </ul>
    </nav>
  </div>
</header>

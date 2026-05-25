<header class="site-header">
  <div class="header-frame">
    <div class="header-inner banner-split">
      <img src="{{ asset('header_banner_left.png') }}"
           alt="Mini License Plates"
           class="site-banner site-banner-left">
      <img src="{{ asset('header_banner_right.png') }}"
           alt="Mini License Plates"
           class="site-banner site-banner-right">
    </div>
  </div>

  <!-- NEW wrapper that is as wide as the content area and filled with white -->
  <div class="nav-outer">
    <nav class="main-nav">
      <ul>
        <li><a href="{{ route('home') }}">HOME</a></li>
        <li><a href="{{ route('gallery') }}">GALLERY</a></li>
        <li><a href="{{ route('about') }}">ABOUT</a></li>
        <li><a href="{{ route('history') }}">HISTORY</a></li>
        <li><a href="{{ route('contribute') }}">CONTRIBUTE</a></li>
        <li><a href="https://www.ebay.com/str/minilicenseplates" target="new">SHOP</a></li>
      </ul>
    </nav>
  </div>
</header>

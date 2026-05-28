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
    <div class="site-nav-block">
      <nav class="main-nav">
        <ul>
          <li><a href="{{ route('home') }}">HOME</a></li>
          <li><a href="{{ route('gallery') }}">GALLERY</a></li>
          <li><a href="{{ route('search') }}">SEARCH</a></li>
          <li><a href="{{ route('about') }}">ABOUT</a></li>
          <li><a href="{{ route('history') }}">HISTORY</a></li>
          <li><a href="{{ route('articles.index') }}">ARTICLES</a></li>
          <li><a href="{{ route('contribute') }}">CONTRIBUTE</a></li>
          @auth
          <li><a href="{{ route('collection.index') }}">MY COLLECTION</a></li>
          <li><a href="{{ route('profile.edit') }}">PROFILE</a></li>
          @if (auth()->user()->isAdmin())
          <li><a href="{{ route('admin.dashboard') }}">ADMIN</a></li>
          @endif
          @else
          <li><a href="{{ route('login') }}">SIGN IN</a></li>
          @endauth
          <li><a href="https://www.ebay.com/str/minilicenseplates" target="new">SHOP</a></li>
        </ul>
      </nav>

      @auth
      <div class="session-bar-row">
        <a href="{{ route('profile.edit') }}" class="session-bar">
          @if (auth()->user()->profileImageUrl())
            <img src="{{ auth()->user()->profileImageUrl() }}"
                 alt=""
                 class="session-bar-avatar">
          @else
            <span class="session-bar-avatar session-bar-avatar-placeholder" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
          @endif
          <span class="session-bar-text">
            Logged in as <strong>{{ auth()->user()->username }}</strong>
          </span>
        </a>
        @if (auth()->user()->isAdmin())
          <a href="{{ route('admin.dashboard') }}" class="session-bar-admin-link">admin</a>
        @endif
        <form method="post" action="{{ route('logout') }}" class="session-bar-logout-form">
          @csrf
          <button type="submit" class="session-bar-logout-btn">Log out</button>
        </form>
      </div>
      @endauth
    </div>
  </div>
</header>

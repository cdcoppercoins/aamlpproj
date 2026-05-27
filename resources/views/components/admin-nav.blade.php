<nav class="admin-nav" aria-label="Administration">
    <ul class="admin-nav-list">
        <li>
            <a href="{{ route('admin.dashboard') }}" @class(['is-active' => request()->routeIs('admin.dashboard')])>
                Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('admin.home-hero.index') }}" @class(['is-active' => request()->routeIs('admin.home-hero.*')])>
                Home banners
            </a>
        </li>
        <li>
            <a href="{{ route('admin.catalog.sets.index') }}" @class(['is-active' => request()->routeIs('admin.catalog.*')])>
                Catalog
            </a>
        </li>
        <li>
            <a href="{{ route('admin.users.index') }}" @class(['is-active' => request()->routeIs('admin.users.*')])>
                Members
            </a>
        </li>
        <li>
            <a href="{{ route('admin.newsletter.index') }}" @class(['is-active' => request()->routeIs('admin.newsletter.*')])>
                Newsletter
            </a>
        </li>
        <li class="admin-nav-spacer" aria-hidden="true"></li>
        <li>
            <a href="{{ route('home') }}">View site</a>
        </li>
    </ul>
</nav>

<aside id="sidebar" class="customer-sidebar" aria-label="Marketplace navigation">
    <a href="{{ route('home') }}" class="customer-brand">
        <span class="customer-brand-mark">◒</span>
        <span>drypp</span>
        <small>Marketplace</small>
    </a>
    <div class="customer-nav-label">Explore</div>
    <nav class="customer-nav">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
            <span class="customer-nav-icon">⌂</span>Home
        </a>
        <a href="{{ route('domains.search') }}" class="{{ request()->routeIs('domains.*') ? 'active' : '' }}">
            <span class="customer-nav-icon">⌕</span>Search domains
        </a>
        <a href="{{ route('services') }}" class="{{ request()->routeIs('services') ? 'active' : '' }}">
            <span class="customer-nav-icon">▣</span>Services
        </a>
        <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*') ? 'active' : '' }}">
            <span class="customer-nav-icon">&#x1F6D2;</span>Your cart
        </a>
    </nav>
    <div class="customer-sidebar-footer">
        <div class="customer-nav">
            <a href="{{ route('login') }}">Log in to your account</a>
            <a href="{{ route('register') }}" class="active">Create an account</a>
        </div>
    </div>
    <button type="button" id="closeSidebar" aria-label="Close navigation" style="display:none">×</button>
</aside>

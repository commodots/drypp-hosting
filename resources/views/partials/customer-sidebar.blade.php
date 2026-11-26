<aside id="sidebar" class="customer-sidebar" aria-label="Customer navigation">
    <a href="{{ route('dashboard') }}" class="customer-brand">
        <span class="customer-brand-mark">◒</span>
        <span>drypp</span>
        <small>Workspace</small>
    </a>
    <div class="customer-nav-label">Workspace</div>
    <nav class="customer-nav">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><span
                class="customer-nav-icon">▦</span>Dashboard</a>
        <a href="{{ route('campaigns.index') }}" class="{{ request()->routeIs('campaigns.*') ? 'active' : '' }}"><span
                class="customer-nav-icon">✉</span>Email campaigns</a>
        <a href="{{ route('contacts.index') }}"
            class="{{ request()->routeIs('contacts.*', 'groups.*') ? 'active' : '' }}"><span
                class="customer-nav-icon">♙</span>Contacts & groups</a>
        <a href="{{ route('automation.index') }}" class="{{ request()->routeIs('automation.*') ? 'active' : '' }}"><span
                class="customer-nav-icon">↻</span>Automations</a>
        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><span
                class="customer-nav-icon">▥</span>Reports</a>
    </nav>
    <div class="customer-nav-label">Services</div>
    <nav class="customer-nav">
        <a href="{{ route('my-services.index') }}"
            class="{{ request()->routeIs('my-services.*') ? 'active' : '' }}"><span
                class="customer-nav-icon">▣</span>My Services</a>
        <a href="{{ route('hosting.index') }}" class="{{ request()->routeIs('hosting.*') ? 'active' : '' }}"><span
                class="customer-nav-icon">⌂</span>Domains & Hosting</a>
        <a href="{{ route('billing.index') }}" class="{{ request()->routeIs('billing.*') ? 'active' : '' }}"><span
                class="customer-nav-icon">＄</span>Billing & Orders</a>
        <a href="{{ route('leads.index') }}" class="{{ request()->routeIs('leads.*') ? 'active' : '' }}"><span
                class="customer-nav-icon">◎</span>Lead Generation</a>
    </nav>
    <div class="customer-nav-label">Account</div>
    <nav class="customer-nav">
        <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}"><span
                class="customer-nav-icon">⚙</span>Settings</a>
        @if (auth()->user()?->role === 'admin')
            <a href="{{ route('admin.dashboard') }}"><span class="customer-nav-icon">▤</span>Admin portal</a>
        @endif
        <a href="{{ route('home') }}"><span class="customer-nav-icon">↗</span>View website</a>
    </nav>
    <div class="customer-sidebar-footer">
        <div class="customer-account">
            <span class="customer-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
            <span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="customer-logout">↪ &nbsp; Sign out</button>
        </form>
    </div>
    <button type="button" id="closeSidebar" aria-label="Close navigation" style="display:none">×</button>
</aside>

<aside id="sidebar" class="admin-sidebar" aria-label="Admin navigation">
    <a href="{{ route('admin.dashboard') }}" class="admin-brand">
        <span class="admin-brand-mark">◒</span><span>drypp</span><small>Admin</small>
    </a>
    <div class="admin-nav-label">Workspace</div>
    <nav class="admin-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span
                class="admin-nav-icon">▦</span>Dashboard</a>
        <a href="{{ route('admin.customers.index') }}"
            class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"><span
                class="admin-nav-icon">♙</span>Customers</a>
        <a href="{{ route('admin.payments.index') }}"
            class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"><span
                class="admin-nav-icon">＄</span>Payments</a>
    </nav>
    <div class="admin-nav-label" style="margin-top:18px">Services</div>
    <nav class="admin-nav">
        <a href="{{ route('admin.campaigns.index') }}"
            class="{{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}"><span
                class="admin-nav-icon">◉</span>Campaigns</a>
        <a href="{{ route('admin.packages.index') }}"
            class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}"><span
                class="admin-nav-icon">◇</span>Manage Packages</a>
        <a href="{{ route('admin.smtps.index') }}"
            class="{{ request()->routeIs('admin.smtps.*') ? 'active' : '' }}"><span class="admin-nav-icon">⇄</span>SMTP
            Management</a>
        <a href="{{ route('admin.hosting.index') }}"
            class="{{ request()->routeIs('admin.hosting.index') ? 'active' : '' }}"><span
                class="admin-nav-icon">▤</span>Hosting Control Centre</a>
        <a href="{{ route('admin.hosting.packages') }}"
            class="{{ request()->routeIs('admin.hosting.packages*') ? 'active' : '' }}"><span
                class="admin-nav-icon">◇</span>Manage Hosting Packages</a>
        <a href="{{ route('admin.hosting.servers') }}"
            class="{{ request()->routeIs('admin.hosting.servers*') ? 'active' : '' }}"><span
                class="admin-nav-icon">▥</span>Server Configuration</a>
        <a href="{{ route('admin.domain-pricing.index') }}"
            class="{{ request()->routeIs('admin.domain-pricing.*') ? 'active' : '' }}"><span
                class="admin-nav-icon">◎</span>Domain Pricing</a>
        <a href="{{ route('admin.domains.index') }}"
            class="{{ request()->routeIs('admin.domains.*') ? 'active' : '' }}"><span
                class="admin-nav-icon">⌂</span>Domain Management</a>
        <a href="{{ route('admin.hosting.accounts') }}"
            class="{{ request()->routeIs('admin.hosting.accounts') ? 'active' : '' }}"><span
                class="admin-nav-icon">▣</span>Hosting Accounts</a>
        <a href="{{ route('admin.provisioning.index') }}"
            class="{{ request()->routeIs('admin.provisioning.*') ? 'active' : '' }}"><span
                class="admin-nav-icon">↻</span>Provisioning Queue</a>
        <a href="{{ route('admin.support-renewals.index') }}"
            class="{{ request()->routeIs('admin.support-renewals.*') ? 'active' : '' }}"><span
                class="admin-nav-icon">◷</span>Support & Renewals</a>
    </nav>
    <div class="admin-nav-label" style="margin-top:18px">Account</div>
    <nav class="admin-nav">
        <a href="{{ route('home') }}"><span class="admin-nav-icon">↗</span>View website</a>
        <a href="{{ route('dashboard') }}"><span class="admin-nav-icon">⌂</span>Customer view</a>
    </nav>
    <div class="admin-sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="admin-logout">↪ &nbsp;
                Sign out</button></form>
        <div style="padding:5px 12px 0">Drypp Admin · {{ date('Y') }}</div>
    </div>
    <button type="button" id="closeSidebar" aria-label="Close navigation" style="display:none">×</button>
</aside>

<aside id="sidebar" class="admin-sidebar" aria-label="Admin navigation">
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-brand">
        <span class="admin-brand-mark">◒</span><span>drypp</span><small>Admin</small>
    </a>
    <div class="admin-nav-label">Workspace</div>
    <nav class="admin-nav">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="<?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>"><span class="admin-nav-icon">▦</span>Dashboard</a>
        <a href="<?php echo e(route('admin.campaigns.index')); ?>" class="<?php echo e(request()->routeIs('admin.campaigns.*') ? 'active' : ''); ?>"><span class="admin-nav-icon">◉</span>Campaigns</a>
        <a href="<?php echo e(route('admin.packages.index')); ?>" class="<?php echo e(request()->routeIs('admin.packages.*') ? 'active' : ''); ?>"><span class="admin-nav-icon">◇</span>Manage Packages</a>
        <a href="<?php echo e(route('admin.smtps.index')); ?>" class="<?php echo e(request()->routeIs('admin.smtps.*') ? 'active' : ''); ?>"><span class="admin-nav-icon">⇄</span>SMTP Management</a>
        <a href="<?php echo e(route('admin.payments.index')); ?>" class="<?php echo e(request()->routeIs('admin.payments.*') ? 'active' : ''); ?>"><span class="admin-nav-icon">＄</span>Payments</a>
        <a href="<?php echo e(route('admin.hosting.index')); ?>" class="<?php echo e(request()->routeIs('admin.hosting.*') ? 'active' : ''); ?>"><span class="admin-nav-icon">▤</span>Hosting Control Centre</a>
    </nav>
    <div class="admin-nav-label" style="margin-top:18px">Account</div>
    <nav class="admin-nav">
        <a href="<?php echo e(route('home')); ?>"><span class="admin-nav-icon">↗</span>View website</a>
        <a href="<?php echo e(route('dashboard')); ?>"><span class="admin-nav-icon">⌂</span>Customer view</a>
    </nav>
    <div class="admin-sidebar-footer">
        <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button type="submit" class="admin-logout">↪ &nbsp; Sign out</button></form>
        <div style="padding:5px 12px 0">Drypp Admin · <?php echo e(date('Y')); ?></div>
    </div>
    <button type="button" id="closeSidebar" aria-label="Close navigation" style="display:none">×</button>
</aside>
<?php /**PATH C:\xampp\htdocs\drypp\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>
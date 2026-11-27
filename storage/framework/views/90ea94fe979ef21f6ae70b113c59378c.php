<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Drypp Admin'); ?> · Drypp</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <style>
        :root {
            --admin-navy: #0c1730;
            --admin-navy-2: #142747;
            --admin-blue: #4169f5;
            --admin-cyan: #48c9dc;
            --admin-ink: #17233d;
            --admin-muted: #71809b;
            --admin-line: #e4eaf4;
            --admin-bg: #f3f6fc
        }

        body.admin-body {
            margin: 0;
            background: var(--admin-bg);
            color: var(--admin-ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif
        }

        .admin-frame {
            min-height: 100vh;
            display: flex
        }

        .admin-sidebar {
            width: 258px;
            flex: 0 0 258px;
            min-height: 100vh;
            background: linear-gradient(180deg, #101c35 0%, #0a1429 100%);
            color: #dce6fb;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 60;
            box-shadow: 12px 0 32px rgba(12, 23, 48, .08)
        }

        .admin-sidebar {
            overflow-y: auto
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 3px 10px 28px;
            color: #fff;
            font-size: 23px;
            font-weight: 800;
            letter-spacing: -.7px
        }

        .admin-brand-mark {
            height: 36px;
            width: 36px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--admin-blue), var(--admin-cyan));
            box-shadow: 0 8px 20px #4169f54d;
            color: white;
            font-size: 18px
        }

        .admin-brand small {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .12em;
            color: #90a2c4;
            text-transform: uppercase;
            margin-left: auto
        }

        .admin-nav-label {
            padding: 12px 12px 8px;
            color: #8193b4;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase
        }

        .admin-nav {
            display: grid;
            gap: 5px
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            border-radius: 11px;
            color: #c5d1e8;
            font-size: 13px;
            font-weight: 600;
            transition: background .18s, color .18s, transform .18s
        }

        .admin-nav a:hover {
            background: #ffffff12;
            color: #fff;
            transform: translateX(2px)
        }

        .admin-nav a.active {
            background: linear-gradient(100deg, #4169f5 0%, #3154d7 100%);
            color: #fff;
            box-shadow: 0 8px 20px #3154d744
        }

        .admin-nav-icon {
            width: 20px;
            height: 20px;
            display: inline-grid;
            place-items: center;
            color: inherit;
            opacity: .95;
            font-size: 15px
        }

        .admin-sidebar-footer {
            margin-top: auto;
            padding: 16px 10px 4px;
            border-top: 1px solid #ffffff18;
            color: #91a2c0;
            font-size: 11px;
            line-height: 1.6
        }

        .admin-logout {
            width: 100%;
            border: 0;
            background: transparent;
            color: #c5d1e8;
            text-align: left;
            font: inherit;
            cursor: pointer;
            padding: 11px 12px;
            border-radius: 11px
        }

        .admin-logout:hover {
            background: #ef44441a;
            color: #fecaca
        }

        .admin-main-wrap {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column
        }

        .admin-topbar {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 clamp(18px, 3vw, 38px);
            background: #fff;
            border-bottom: 1px solid var(--admin-line);
            position: sticky;
            top: 0;
            z-index: 30
        }

        .admin-topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0
        }

        .admin-kicker {
            font-size: 11px;
            color: #8190aa;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase
        }

        .admin-top-title {
            font-size: 16px;
            font-weight: 750;
            color: #17233d;
            letter-spacing: -.02em
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #52617d;
            font-size: 12px;
            font-weight: 650
        }

        .admin-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #e9efff;
            color: #3157df;
            font-weight: 800
        }

        .admin-content {
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px clamp(18px, 3vw, 38px) 48px;
            flex: 1
        }

        .admin-alert {
            margin: 0 0 18px;
            padding: 13px 16px;
            border: 1px solid;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 650
        }

        .admin-alert-success {
            background: #eafaf3;
            border-color: #c7efdf;
            color: #137b55
        }

        .admin-alert-error {
            background: #fff1f1;
            border-color: #ffd9d9;
            color: #a32929
        }

        .admin-alert-info {
            background: #eef4ff;
            border-color: #cfddff;
            color: #3153a5
        }

        .admin-page-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 16px;
            flex-wrap: wrap;
            margin: 0 0 22px
        }

        .admin-page-heading h1 {
            margin: 0;
            color: #17233d;
            font-size: clamp(23px, 3vw, 30px);
            font-weight: 800;
            letter-spacing: -.04em
        }

        .admin-page-heading p {
            margin: 6px 0 0;
            color: #71809b;
            font-size: 13px;
            line-height: 1.55
        }

        .admin-panel {
            min-width: 0;
            margin: 0 0 18px;
            padding: 21px;
            background: #fff;
            border: 1px solid var(--admin-line);
            border-radius: 16px;
            box-shadow: 0 8px 24px #182e5508
        }

        .admin-panel h2 {
            margin: 0 0 15px;
            color: #182846;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -.02em
        }

        .admin-table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #e7edf6;
            border-radius: 12px
        }

        .admin-table {
            width: 100%;
            min-width: 680px;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
            color: #4d5d79
        }

        .admin-table th {
            padding: 12px 14px;
            background: #f5f7fc;
            border-bottom: 1px solid #e5ebf5;
            color: #75839d;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: 10px;
            font-weight: 850;
            white-space: nowrap
        }

        .admin-table td {
            padding: 13px 14px;
            border-bottom: 1px solid #edf1f7;
            vertical-align: middle
        }

        .admin-table tbody tr:last-child td {
            border-bottom: 0
        }

        .admin-table tbody tr:hover {
            background: #fafbff
        }

        .admin-table td strong {
            color: #1b2b49
        }

        .admin-empty {
            padding: 24px;
            border: 1px dashed #cfd9e9;
            border-radius: 12px;
            background: #fafbfe;
            color: #71809b;
            text-align: center;
            font-size: 13px
        }

        .admin-status {
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border-radius: 99px;
            background: #eaf0ff;
            color: #3458d8;
            font-size: 10px;
            font-weight: 800;
            white-space: nowrap
        }

        .admin-status[data-state="failed"],
        .admin-status[data-state="suspended"] {
            background: #fff0f0;
            color: #a32929
        }

        .admin-status[data-state="active"],
        .admin-status[data-state="paid"] {
            background: #eafaf3;
            color: #137b55
        }

        .admin-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 0 13px;
            border: 1px solid #dce5f3;
            border-radius: 9px;
            background: #fff;
            color: #31415e;
            font-size: 12px;
            font-weight: 750;
            text-decoration: none;
            cursor: pointer
        }

        .admin-button-primary {
            border-color: #3155dc;
            background: linear-gradient(110deg, #4169f5, #3155dc);
            color: #fff
        }

        .admin-button-danger {
            border-color: #f3c8c8;
            background: #fff7f7;
            color: #a32929
        }

        .admin-button:focus-visible {
            outline: 3px solid #4169f54d;
            outline-offset: 2px
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px
        }

        @media(max-width:700px) {
            .admin-grid {
                grid-template-columns: 1fr
            }

            .admin-panel {
                padding: 16px
            }
        }

        .admin-mobile-toggle {
            display: none;
            border: 1px solid var(--admin-line);
            background: #fff;
            color: #243653;
            border-radius: 10px;
            width: 38px;
            height: 38px;
            font-size: 19px;
            cursor: pointer
        }

        .admin-overlay {
            display: none
        }

        @media(max-width:900px) {
            .admin-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                transform: translateX(-105%);
                transition: transform .22s ease;
                width: 270px;
                height: 100dvh
            }

            .admin-sidebar.is-open {
                transform: translateX(0)
            }

            .admin-overlay.is-open {
                display: block;
                position: fixed;
                inset: 0;
                background: #08122699;
                z-index: 50
            }

            .admin-mobile-toggle {
                display: grid;
                place-items: center
            }

            .admin-content {
                padding-top: 22px
            }
        }

        @media(max-width:560px) {
            .admin-topbar {
                height: 62px;
                padding: 0 14px
            }

            .admin-user-email {
                display: none
            }

            .admin-content {
                padding: 18px 13px 34px
            }
        }
    </style>
</head>

<body class="admin-body">
    <div class="admin-frame">
        <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="admin-overlay" id="adminSidebarOverlay"></div>
        <div class="admin-main-wrap">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="admin-mobile-toggle" id="adminSidebarToggle"
                        aria-label="Open navigation" aria-expanded="false">☰</button>
                    <div>
                        <div class="admin-kicker">Drypp / Administration</div>
                        <div class="admin-top-title"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></div>
                    </div>
                </div>
                <div class="admin-user"><span
                        class="admin-avatar"><?php echo e(strtoupper(substr(auth()->user()->name ?? 'A', 0, 1))); ?></span><span
                        class="admin-user-email"><?php echo e(auth()->user()->email ?? ''); ?></span></div>
            </header>
            <main class="admin-content">
                <?php if(session('status') || session('success')): ?>
                    <div class="admin-alert admin-alert-success" role="status">
                        <?php echo e(session('status') ?: session('success')); ?></div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="admin-alert admin-alert-error" role="alert"><?php echo e(session('error')); ?></div>
                <?php endif; ?>
                <?php if($errors->any()): ?>
                    <div class="admin-alert admin-alert-error" role="alert"><?php echo e($errors->first()); ?></div>
                <?php endif; ?>
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script>
        (function() {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('adminSidebarToggle');
            const close = document.getElementById('closeSidebar');
            const overlay = document.getElementById('adminSidebarOverlay');

            function setOpen(open) {
                if (!sidebar) return;
                sidebar.classList.toggle('is-open', open);
                overlay?.classList.toggle('is-open', open);
                toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
            }
            toggle?.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open')));
            close?.addEventListener('click', () => setOpen(false));
            overlay?.addEventListener('click', () => setOpen(false));
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') setOpen(false)
            });
            document.querySelectorAll('.admin-content form').forEach(form => form.addEventListener('submit', () => {
                const button = form.querySelector('button[type="submit"],button:not([type])');
                if (!button) return;
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                button.dataset.originalLabel = button.textContent;
                button.textContent = button.dataset.loadingLabel || 'Working...';
            }));
        })();
    </script>
</body>

</html>
<?php /**PATH C:\Users\NEMO\drypp-hosting\resources\views/layouts/app.blade.php ENDPATH**/ ?>
<?php
require_once __DIR__ . '/../includes/auth.php';

function admin_nav_active(string $file): string {
    $current = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    return $current === $file ? ' class="active" aria-current="page"' : '';
}

function admin_header(string $title): void { apply_security_headers(); ensure_phase6_security_schema(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0d1017">
    <title><?= e($title) ?> | Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=<?= e(app_asset_version()) ?>">
</head>
<body class="admin-body">
<div class="admin-backdrop" data-admin-close aria-hidden="true"></div>
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">
        <div class="admin-sidebar-head">
            <a class="admin-logo admin-logo-lockup" href="index.php" aria-label="A TO Z SIGNAL Admin Dashboard">
                <span class="admin-brand-images">
                    <img src="../assets/img/logo-atoz.webp" alt="A TO Z" width="1598" height="1041">
                    <img src="../assets/img/logo-signal.webp" alt="SIGNAL" width="2047" height="880">
                </span>
                <span class="admin-logo-text">Control Panel</span>
            </a>
            <button class="sidebar-close" type="button" data-admin-close aria-label="Close menu">×</button>
        </div>

        <nav class="admin-nav" aria-label="Main admin menu">
            <a<?= admin_nav_active('index.php') ?> href="index.php"><span>▦</span>Dashboard</a>
            <a<?= admin_nav_active('products.php') ?> href="products.php"><span>▣</span>Products</a>
            <a<?= admin_nav_active('product-form.php') ?> href="product-form.php"><span>＋</span>Add Product</a>
            <a<?= admin_nav_active('enquiries.php') ?> href="enquiries.php"><span>✉</span>Lead CRM</a>
            <a<?= admin_nav_active('media.php') ?> href="media.php"><span>▧</span>Media Library</a>
            <a<?= admin_nav_active('content.php') ?> href="content.php"><span>✦</span>Content CMS</a>
            <a<?= admin_nav_active('content-blocks.php') ?> href="content-blocks.php"><span>▥</span>Content Blocks</a>
            <a<?= admin_nav_active('team.php') ?> href="team.php"><span>♚</span>Team</a>
            <a<?= admin_nav_active('timeline.php') ?> href="timeline.php"><span>◷</span>Timeline</a>
            <a<?= admin_nav_active('seo.php') ?> href="seo.php"><span>◎</span>SEO & Marketing</a>
            <a<?= admin_nav_active('maintenance.php') ?> href="maintenance.php"><span>⛨</span>Security & Maintenance</a>
            <a<?= admin_nav_active('blogs.php') ?> href="blogs.php"><span>▤</span>Blog / Archive</a>
            <a<?= admin_nav_active('blog-form.php') ?> href="blog-form.php"><span>✎</span>Add Blog Post</a>
            <a<?= admin_nav_active('settings.php') ?> href="settings.php"><span>⚙</span>Settings</a>
            <a<?= admin_nav_active('profile.php') ?> href="profile.php"><span>👤</span>Admin Profile</a>
        </nav>

        <div class="admin-sidebar-tools">
            <a class="sidebar-site-link" href="<?= e(base_url()) ?>" target="_blank" rel="noopener">View Website ↗</a>
            <a class="sidebar-logout" href="logout.php">Logout</a>
        </div>
    </aside>

    <main class="admin-main" id="adminMain">
        <header class="admin-top">
            <div class="admin-top-left">
                <button class="mobile-menu-btn" type="button" data-admin-open aria-controls="adminSidebar" aria-expanded="false" aria-label="Open admin menu">☰</button>
                <div>
                    <p class="admin-kicker">A TO Z & SIGNAL</p>
                    <h1><?= e($title) ?></h1>
                </div>
            </div>
            <a class="admin-user-pill" href="profile.php" title="Edit admin profile">
                <?php $avatar = current_admin_avatar(); ?>
                <span class="admin-user-avatar"><?php if($avatar): ?><img src="../<?= e($avatar) ?>" alt="<?= e(current_admin_name()) ?>"><?php else: ?><?= e(admin_initials(current_admin_name())) ?><?php endif; ?></span>
                <b><?= e(current_admin_name()) ?></b>
            </a>
        </header>
<?php }

function admin_footer(): void { ?>
    </main>
</div>
<script src="../assets/js/admin.js?v=<?= e(app_asset_version()) ?>"></script>
</body>
</html>
<?php }

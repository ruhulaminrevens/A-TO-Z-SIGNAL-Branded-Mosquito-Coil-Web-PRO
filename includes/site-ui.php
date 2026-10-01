<?php
if (!function_exists('public_asset_version')) {
    function public_asset_version(): string { return app_asset_version(); }
}

function public_nav_active(string $active, string $key): string {
    return $active === $key ? ' class="active" aria-current="page"' : '';
}

function public_smoke_spans(): string {
    $spans = '';
    for ($i = 0; $i < 28; $i++) {
        $left = ($i * 17) % 97;
        $size = 70 + (($i * 31) % 116);
        $duration = 9 + ($i % 8);
        $delay = -1 * (($i * 2) % 10);
        $x = -80 + (($i * 29) % 160);
        $spans .= '<span style="--l:' . $left . '%;--s:' . $size . 'px;--d:' . $duration . 's;--delay:' . $delay . 's;--x:' . $x . 'px"></span>' . "\n";
    }
    return $spans;
}

function public_header(array $settings, string $title, string $description, string $active = '', string $canonicalOverride = ''): void {
    apply_security_headers();
    ensure_phase5_marketing_schema();
    $siteName = $settings['site_name'] ?? 'A TO Z & SIGNAL Mosquito Coil';
    $phone = $settings['contact_phone'] ?? '';
    $catalogue = catalogue_download_url($settings, null, 'Floating Button');
    $whatsapp = $settings['whatsapp_number'] ?? '';
    $facebook = $settings['facebook'] ?? '#';
    $fullTitle = seo_title($title, $settings);
    $description = seo_description($description);
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $canonical = $canonicalOverride ?: current_canonical_url(); $ogImage = optimized_image_url(cms_setting($settings, 'global_og_image') ?: 'assets/img/hero-atoz-pack.png'); ?>
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
    <?php if(cms_setting($settings, 'google_site_verification')): ?><meta name="google-site-verification" content="<?= e(cms_setting($settings, 'google_site_verification')) ?>"><?php endif; ?>
    <?php if(cms_setting($settings, 'global_seo_keywords')): ?><meta name="keywords" content="<?= e(cms_setting($settings, 'global_seo_keywords')) ?>"><?php endif; ?>
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($fullTitle) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_BD">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($fullTitle) ?>">
    <meta name="twitter:description" content="<?= e($description) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <meta name="theme-color" content="#07080c">
    <link rel="icon" type="image/png" href="<?= e(asset_url('assets/img/favicon-atoz.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Bengali:wght@400;500;600;700;800;900&family=Tiro+Bangla:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>?v=<?= e(public_asset_version()) ?>">
    <?= json_ld(organization_schema($settings)) ?>
    <?= json_ld(website_schema($settings)) ?>
    <?= json_ld(webpage_schema($fullTitle, $description, $canonical)) ?>
    <?php if(!empty($settings['google_analytics_id'])): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($settings['google_analytics_id']) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($settings['google_analytics_id']) ?>');</script>
    <?php endif; ?>
    <?php if(!empty($settings['meta_pixel_id'])): ?>
    <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?= e($settings['meta_pixel_id']) ?>');fbq('track','PageView');</script>
    <?php endif; ?>
</head>
<body data-theme="dark" data-lang="en">
<a class="skip-link" href="#main-content">Skip to main content</a>
<div class="ambient-bg" aria-hidden="true"></div>
<div class="site-bg" aria-hidden="true"></div>
<div class="smoke" aria-hidden="true"><?= public_smoke_spans() ?></div>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand-lockup" href="<?= e(home_url('home')) ?>" aria-label="A TO Z and SIGNAL home">
            <span class="brand-logo brand-logo-atoz"><img src="<?= e(asset_url('assets/img/logo-atoz.webp')) ?>" alt="A TO Z" width="1598" height="1041"></span>
            <span class="brand-logo brand-logo-signal"><img src="<?= e(asset_url('assets/img/logo-signal.webp')) ?>" alt="SIGNAL" width="2047" height="880"></span>
        </a>
        <nav class="desktop-nav" aria-label="Main navigation">
            <a<?= public_nav_active($active, 'home') ?> href="<?= e(home_url('home')) ?>" data-en="Home" data-bn="হোম">Home</a>
            <a<?= public_nav_active($active, 'products') ?> href="<?= e(home_url('products')) ?>" data-en="Products" data-bn="পণ্য">Products</a>
            <a href="<?= e(home_url('compare')) ?>" data-en="Compare" data-bn="তুলনা">Compare</a>
            <a href="<?= e(home_url('technology')) ?>" data-en="Features" data-bn="ফিচার">Features</a>
            <a href="<?= e(home_url('story')) ?>" data-en="Our Story" data-bn="আমাদের গল্প">Our Story</a>
            <a href="<?= e(home_url('network')) ?>" data-en="Distributors" data-bn="ডিস্ট্রিবিউটর">Distributors</a>
            <a href="<?= e(home_url('contact')) ?>" data-en="Contact" data-bn="যোগাযোগ">Contact</a>
            <a<?= public_nav_active($active, 'about') ?> href="<?= e(page_url('about')) ?>" data-en="About Us" data-bn="আমাদের সম্পর্কে">About Us</a>
            <a<?= public_nav_active($active, 'blogs') ?> href="<?= e(page_url('blogs')) ?>" data-en="Blogs" data-bn="ব্লগ">Blogs</a>
        </nav>
        <div class="nav-actions">
            <button class="icon-btn" id="themeToggle" type="button" aria-label="Toggle dark and light mode"><span>☀</span></button>
            <button class="lang-btn" id="langToggle" type="button" aria-label="Toggle English and Bangla"><span>BN</span></button>
            <?php if($phone): ?><a class="pill-link phone-pill" href="tel:<?= e($phone) ?>">☎ <?= e($phone) ?></a><?php endif; ?>
            <button class="mobile-menu-btn" id="menuToggle" type="button" aria-label="Open menu">☰</button>
        </div>
    </div>
    <div class="mobile-panel" id="mobilePanel" aria-hidden="true">
        <div class="mobile-panel-head">
            <span data-en="Menu" data-bn="মেনু">Menu</span>
            <button type="button" class="mobile-panel-close" id="menuClose" aria-label="Close menu">×</button>
        </div>
        <a href="<?= e(home_url('home')) ?>" data-en="Home" data-bn="হোম">Home</a>
        <a href="<?= e(home_url('products')) ?>" data-en="Products" data-bn="পণ্য">Products</a>
        <a href="<?= e(home_url('compare')) ?>" data-en="Compare" data-bn="তুলনা">Compare</a>
        <a href="<?= e(home_url('technology')) ?>" data-en="Features" data-bn="ফিচার">Features</a>
        <a href="<?= e(home_url('story')) ?>" data-en="Our Story" data-bn="আমাদের গল্প">Our Story</a>
        <a href="<?= e(home_url('network')) ?>" data-en="Distributors" data-bn="ডিস্ট্রিবিউটর">Distributors</a>
        <a href="<?= e(home_url('contact')) ?>" data-en="Contact" data-bn="যোগাযোগ">Contact</a>
        <a<?= public_nav_active($active, 'about') ?> href="<?= e(page_url('about')) ?>" data-en="About Us" data-bn="আমাদের সম্পর্কে">About Us</a>
        <a<?= public_nav_active($active, 'blogs') ?> href="<?= e(page_url('blogs')) ?>" data-en="Blogs" data-bn="ব্লগ">Blogs</a>
    </div>
</header>
<div class="floating-social" aria-label="Quick contact links">
    <a href="<?= e($catalogue) ?>" title="Catalogue">🛒</a>
    <a href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener" title="WhatsApp">☘</a>
    <a href="<?= e($facebook) ?>" target="_blank" rel="noopener" title="Facebook">f</a>
</div>
<button class="back-to-top" id="backToTop" type="button" aria-label="Back to top">🔝</button>
<?php }

function public_footer(array $settings, array $products = []): void {
    $siteName = $settings['site_name'] ?? 'A TO Z & SIGNAL Mosquito Coil';
    ?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <div class="brand-lockup footer-brand">
                <span class="brand-logo brand-logo-atoz"><img src="<?= e(asset_url('assets/img/logo-atoz.webp')) ?>" alt="A TO Z" width="1598" height="1041"></span>
                <span class="brand-logo brand-logo-signal"><img src="<?= e(asset_url('assets/img/logo-signal.webp')) ?>" alt="SIGNAL" width="2047" height="880"></span>
            </div>
            <p data-en="<?= e(cms_setting($settings, 'footer_description_en')) ?>" data-bn="<?= e(cms_setting($settings, 'footer_description_bn')) ?>"><?= e(cms_setting($settings, 'footer_description_en')) ?></p>
        </div>
        <div>
            <h4>Quick Links</h4>
            <a href="<?= e(home_url('home')) ?>">Home</a>
            <a href="<?= e(home_url('products')) ?>">Products</a>
            <a href="<?= e(page_url('about')) ?>">About Us</a>
            <a href="<?= e(page_url('blogs')) ?>">Blogs</a>
            <a href="<?= e(page_url('privacy')) ?>">Privacy Policy</a>
            <a href="<?= e(page_url('terms')) ?>">Terms & Conditions</a>
            <a href="<?= e(catalogue_download_url($settings, null, 'Footer')) ?>">Catalogue</a>
        </div>
        <div>
            <h4>Our Products</h4>
            <?php foreach(array_slice($products, 0, 7) as $p): ?>
                <a href="<?= e(product_url($p)) ?>"><?= e($p['name_en']) ?></a>
            <?php endforeach; ?>
        </div>
        <div>
            <h4>Contact Info</h4>
            <a href="tel:<?= e($settings['contact_phone'] ?? '') ?>">Phone<br><?= e($settings['contact_phone'] ?? '') ?></a>
            <a href="https://wa.me/<?= e($settings['whatsapp_number'] ?? '') ?>" target="_blank" rel="noopener">WhatsApp<br>+<?= e($settings['whatsapp_number'] ?? '') ?></a>
            <a href="mailto:<?= e($settings['email'] ?? '') ?>">Email<br><?= e($settings['email'] ?? '') ?></a>
            <a href="<?= e(catalogue_download_url($settings, null, 'Footer Contact')) ?>">Catalogue<br><?= e(cms_setting($settings, 'catalogue_version')) ?></a>
        </div>
    </div>
    <div class="copyright">© <?= date('Y') ?> <?= e($siteName) ?>. All Rights Reserved.</div>
</footer>
<script>window.ATOZ_ROUTES={productBase:<?= json_encode(rtrim(base_url('product'), '/') . '/') ?>,blogBase:<?= json_encode(rtrim(base_url('blog'), '/') . '/') ?>,catalogue:<?= json_encode(rtrim(base_url('catalogue'), '/')) ?>,home:<?= json_encode(base_url()) ?>};</script>
<script src="<?= e(asset_url('assets/js/main.js')) ?>?v=<?= e(public_asset_version()) ?>" defer></script>
</body>
</html>
<?php }

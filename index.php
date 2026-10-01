<?php
require_once __DIR__ . '/includes/db.php';
apply_security_headers();
$products = get_products(true);
ensure_phase2_cms_schema();
ensure_phase5_marketing_schema();
ensure_phase43_media_content_schema();
$settings = get_site_settings();
$districts = district_list();
$comparisonRows = product_comparison_rows($products);
$catalogueUrl = catalogue_download_url($settings, null, 'Homepage');
$first = $products[0] ?? default_products()[0];

$heroAtoz = $first;
$heroSignal = $products[1] ?? $first;
foreach ($products as $p) {
    if ($p['brand'] === 'A TO Z' && stripos($p['name_en'], 'jumbo') !== false) { $heroAtoz = $p; break; }
}
foreach ($products as $p) {
    if ($p['brand'] === 'SIGNAL' && (stripos($p['name_en'], 'plus') !== false || stripos($p['name_en'], 'mega') !== false)) { $heroSignal = $p; break; }
}
$active = $heroAtoz;
$homeTitle = seo_title(cms_setting($settings, 'home_seo_title'), $settings);
$homeDescription = seo_description(cms_setting($settings, 'home_seo_description'));
$homeCanonical = base_url();
$homeOgImage = optimized_image_url(cms_setting($settings, 'global_og_image') ?: $heroAtoz['image']);
$productJson = [];
foreach ($products as $p) {
    $productJson[] = [
        'id' => (int)($p['id'] ?? 0),
        'brand' => $p['brand'],
        'name_en' => $p['name_en'],
        'name_bn' => $p['name_bn'],
        'slug' => $p['slug'],
        'category' => $p['category'],
        'protection_hours' => $p['protection_hours'],
        'badge' => $p['badge'],
        'short_description_en' => $p['short_description_en'],
        'short_description_bn' => $p['short_description_bn'],
        'features' => json_features($p['features']),
        'image' => optimized_image_url($p['image']),
        'accent_color' => $p['accent_color'] ?: ($p['brand'] === 'SIGNAL' ? '#29d366' : '#f5a623'),
        'pack_size' => $p['pack_size'] ?? '',
        'smoke_type' => $p['smoke_type'] ?? '',
        'room_size' => $p['room_size'] ?? '',
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($homeTitle) ?></title>
    <meta name="description" content="<?= e($homeDescription) ?>">
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
    <?php if(cms_setting($settings, 'google_site_verification')): ?><meta name="google-site-verification" content="<?= e(cms_setting($settings,'google_site_verification')) ?>"><?php endif; ?>
    <?php if(cms_setting($settings, 'global_seo_keywords')): ?><meta name="keywords" content="<?= e(cms_setting($settings, 'global_seo_keywords')) ?>"><?php endif; ?>
    <meta name="theme-color" content="#07080c">
    <meta property="og:title" content="<?= e($homeTitle) ?>">
    <meta property="og:description" content="<?= e($homeDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($homeCanonical) ?>">
    <meta property="og:image" content="<?= e($homeOgImage) ?>">
    <meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"><meta property="og:locale" content="en_BD">
    <meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="<?= e($homeTitle) ?>"><meta name="twitter:description" content="<?= e($homeDescription) ?>"><meta name="twitter:image" content="<?= e($homeOgImage) ?>">
    <link rel="canonical" href="<?= e($homeCanonical) ?>">
    <link rel="preload" as="image" href="<?= e(optimized_image_url('assets/img/hero-atoz-pack.png')) ?>" fetchpriority="high">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="icon" type="image/png" href="<?= e(asset_url('assets/img/favicon-atoz.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans+Bengali:wght@400;500;600;700;800;900&family=Tiro+Bangla:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>?v=<?= e(app_asset_version()) ?>">
    <?= json_ld(organization_schema($settings)) ?>
    <?= json_ld(website_schema($settings)) ?>
    <?= json_ld(webpage_schema($homeTitle, $homeDescription, $homeCanonical)) ?>
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
<div id="sitePreloader" class="site-preloader" aria-label="Loading website">
    <div class="loader-card">
        <div class="loader-brand"><span class="brand-logo brand-logo-atoz"><img src="<?= e(asset_url('assets/img/logo-atoz.webp')) ?>" alt="A TO Z" width="1598" height="1041"></span><span class="brand-logo brand-logo-signal"><img src="<?= e(asset_url('assets/img/logo-signal.webp')) ?>" alt="SIGNAL" width="2047" height="880"></span></div>
        <div class="loader-coil" aria-hidden="true"><span></span><i></i><b></b></div>
        <p data-en="Preparing protection experience..." data-bn="প্রোটেকশন এক্সপেরিয়েন্স প্রস্তুত হচ্ছে...">Preparing protection experience...</p>
        <div class="loader-bar"><span></span></div>
    </div>
</div>
<div class="ambient-bg" aria-hidden="true"></div>
<div class="site-bg" aria-hidden="true"></div>
<div class="smoke" aria-hidden="true"><span style="--l:0%;--s:70px;--d:9s;--delay:-0s;--x:-80px"></span>
<span style="--l:17%;--s:101px;--d:10s;--delay:-2s;--x:-51px"></span>
<span style="--l:34%;--s:132px;--d:11s;--delay:-4s;--x:-22px"></span>
<span style="--l:51%;--s:163px;--d:12s;--delay:-6s;--x:7px"></span>
<span style="--l:68%;--s:74px;--d:13s;--delay:-8s;--x:36px"></span>
<span style="--l:85%;--s:105px;--d:14s;--delay:-0s;--x:65px"></span>
<span style="--l:2%;--s:136px;--d:15s;--delay:-2s;--x:-66px"></span>
<span style="--l:19%;--s:167px;--d:16s;--delay:-4s;--x:-37px"></span>
<span style="--l:36%;--s:78px;--d:9s;--delay:-6s;--x:-8px"></span>
<span style="--l:53%;--s:109px;--d:10s;--delay:-8s;--x:21px"></span>
<span style="--l:70%;--s:140px;--d:11s;--delay:-0s;--x:50px"></span>
<span style="--l:87%;--s:171px;--d:12s;--delay:-2s;--x:79px"></span>
<span style="--l:4%;--s:82px;--d:13s;--delay:-4s;--x:-52px"></span>
<span style="--l:21%;--s:113px;--d:14s;--delay:-6s;--x:-23px"></span>
<span style="--l:38%;--s:144px;--d:15s;--delay:-8s;--x:6px"></span>
<span style="--l:55%;--s:175px;--d:16s;--delay:-0s;--x:35px"></span>
<span style="--l:72%;--s:86px;--d:9s;--delay:-2s;--x:64px"></span>
<span style="--l:89%;--s:117px;--d:10s;--delay:-4s;--x:-67px"></span>
<span style="--l:6%;--s:148px;--d:11s;--delay:-6s;--x:-38px"></span>
<span style="--l:23%;--s:179px;--d:12s;--delay:-8s;--x:-9px"></span>
<span style="--l:40%;--s:90px;--d:13s;--delay:-0s;--x:20px"></span>
<span style="--l:57%;--s:121px;--d:14s;--delay:-2s;--x:49px"></span>
<span style="--l:74%;--s:152px;--d:15s;--delay:-4s;--x:78px"></span>
<span style="--l:91%;--s:183px;--d:16s;--delay:-6s;--x:-53px"></span>
<span style="--l:8%;--s:94px;--d:9s;--delay:-8s;--x:-24px"></span>
<span style="--l:25%;--s:125px;--d:10s;--delay:-0s;--x:5px"></span></div>

<canvas id="particleCanvas" aria-hidden="true"></canvas>
<div class="smoke-layer" aria-hidden="true">
    <?php for ($i=0;$i<16;$i++): ?><span style="--x:<?= rand(2,95) ?>%;--d:<?= rand(16,34) ?>s;--delay:-<?= rand(0,24) ?>s;--drift:<?= rand(-90,90) ?>px"></span><?php endfor; ?>
</div>

<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand-lockup" href="<?= e(home_url('home')) ?>" aria-label="A TO Z and SIGNAL home">
            <span class="brand-logo brand-logo-atoz"><img src="<?= e(asset_url('assets/img/logo-atoz.webp')) ?>" alt="A TO Z" width="1598" height="1041"></span><span class="brand-logo brand-logo-signal"><img src="<?= e(asset_url('assets/img/logo-signal.webp')) ?>" alt="SIGNAL" width="2047" height="880"></span>
        </a>
        <nav class="desktop-nav" aria-label="Main navigation">
            <a class="active" href="<?= e(home_url('home')) ?>" data-en="Home" data-bn="হোম">Home</a>
            <a href="<?= e(home_url('products')) ?>" data-en="Products" data-bn="পণ্য">Products</a>
            <a href="<?= e(home_url('compare')) ?>" data-en="Compare" data-bn="তুলনা">Compare</a>
            <a href="<?= e(home_url('technology')) ?>" data-en="Features" data-bn="ফিচার">Features</a>
            <a href="<?= e(home_url('story')) ?>" data-en="Our Story" data-bn="আমাদের গল্প">Our Story</a>
            <a href="<?= e(home_url('network')) ?>" data-en="Distributors" data-bn="ডিস্ট্রিবিউটর">Distributors</a>
            <a href="<?= e(home_url('contact')) ?>" data-en="Contact" data-bn="যোগাযোগ">Contact</a>
            <a href="<?= e(page_url('about')) ?>" data-en="About Us" data-bn="আমাদের সম্পর্কে">About Us</a>
            <a href="<?= e(page_url('blogs')) ?>" data-en="Blogs" data-bn="ব্লগ">Blogs</a>
        </nav>
        <div class="nav-actions">
            <button class="icon-btn" id="themeToggle" type="button" aria-label="Toggle dark and light mode"><span>☀</span></button>
            <button class="lang-btn" id="langToggle" type="button" aria-label="Toggle English and Bangla"><span>BN</span></button>
            <a class="pill-link phone-pill" href="tel:<?= e($settings['contact_phone']) ?>">☎ <?= e($settings['contact_phone']) ?></a>
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
        <a href="<?= e(page_url('about')) ?>" data-en="About Us" data-bn="আমাদের সম্পর্কে">About Us</a>
        <a href="<?= e(page_url('blogs')) ?>" data-en="Blogs" data-bn="ব্লগ">Blogs</a>
    </div>
</header>

<div class="floating-social" aria-label="Quick contact links">
    <a href="<?= e($catalogueUrl) ?>" title="Catalogue">🛒</a>
    <a href="https://wa.me/<?= e($settings['whatsapp_number']) ?>" target="_blank" rel="noopener" title="WhatsApp">☘</a>
    <a href="<?= e($settings['facebook']) ?>" target="_blank" rel="noopener" title="Facebook">f</a>
</div>
<button class="back-to-top" id="backToTop" type="button" aria-label="Back to top">🔝</button>

<main id="main-content">
<section id="home" class="hero-section hero-v7">
    <div class="container hero-v7-grid">
        <div class="hero-v7-copy reveal">
            <div class="hero-v7-eyebrow trusted">
                <span class="badge-star">✦</span>
                <span data-en="<?= e(cms_setting($settings, 'hero_pill_en')) ?>" data-bn="<?= e(cms_setting($settings, 'hero_pill_bn')) ?>"><?= e(cms_setting($settings, 'hero_pill_en')) ?></span>
            </div>

            <h1 class="hero-v7-title hero-v19-title">
                <span data-en="<?= e(cms_setting($settings, 'hero_title_line1_en')) ?>" data-bn="<?= e(cms_setting($settings, 'hero_title_line1_bn')) ?>"><?= e(cms_setting($settings, 'hero_title_line1_en')) ?></span><br>
                <em data-en="<?= e(cms_setting($settings, 'hero_title_line2_en')) ?>" data-bn="<?= e(cms_setting($settings, 'hero_title_line2_bn')) ?>"><?= e(cms_setting($settings, 'hero_title_line2_en')) ?></em>
            </h1>

            <div class="hero-v7-brandline" aria-label="Featured brands"><span class="brand-word atoz" data-en="A TO Z" data-bn="A TO Z">A TO Z</span><span class="brand-word signal" data-en="SIGNAL" data-bn="SIGNAL">SIGNAL</span></div>

            <p class="hero-v7-text" data-en="<?= e(cms_setting($settings, 'hero_subtitle_en')) ?>" data-bn="<?= e(cms_setting($settings, 'hero_subtitle_bn')) ?>"><?= e(cms_setting($settings, 'hero_subtitle_en')) ?></p>

            <div class="hero-v7-actions">
                <a class="btn btn-red" href="<?= e(home_url('products')) ?>" data-en="<?= e(cms_setting($settings, 'hero_primary_cta_en')) ?>" data-bn="<?= e(cms_setting($settings, 'hero_primary_cta_bn')) ?>"><?= e(cms_setting($settings, 'hero_primary_cta_en')) ?></a>
                <a class="btn btn-ghost" href="<?= e(home_url('network')) ?>" data-en="<?= e(cms_setting($settings, 'hero_secondary_cta_en')) ?>" data-bn="<?= e(cms_setting($settings, 'hero_secondary_cta_bn')) ?>"><?= e(cms_setting($settings, 'hero_secondary_cta_en')) ?></a>
            </div>

            <div class="hero-v7-features">
                <div class="hero-v7-feature">
                    <i>⏰</i>
		    <strong data-en="10–12 Hours" data-bn="১০-১২ ঘণ্টার">10–12 Hours</strong>
                    <span data-en="Full Protection" data-bn="নিশ্চিত সুরক্ষা">Full Protection</span>
                </div>
                <div class="hero-v7-feature">
                    <i>𖦹</i>
                    <strong data-en="Less Smoke" data-bn="কম ধোঁয়া">Less Smoke</strong>
                    <span data-en="Technology" data-bn="টেকনোলজি">Technology</span>
                </div>
                <div class="hero-v7-feature">
                    <i>🛡️</i>
                    <strong data-en="Safeguard" data-bn="নিরাপদ সুরক্ষা">Safeguard</strong>
                    <span data-en="All Families" data-bn="সকল পরিবারের">All Families</span>
                </div>
            </div>
        </div>

        <div class="hero-v7-visual reveal" id="heroVisualV7" aria-label="Premium A TO Z and SIGNAL product showcase">
            <div class="hero-v7-bg-orb hero-v7-red"></div>
            <div class="hero-v7-bg-orb hero-v7-green"></div>
            <div class="hero-v7-floor"></div>
            <div class="hero-v7-floor-light"></div>

            <figure class="hero-v7-pack hero-v7-pack-main" data-parallax="main">
                <span class="pack-aura"></span>
                <img src="<?= e(optimized_image_url('assets/img/hero-atoz-pack.png')) ?>" alt="<?= e($heroAtoz['name_en']) ?> product pack" loading="eager" fetchpriority="high" decoding="async"<?= image_dimension_attributes('assets/img/hero-atoz-pack.webp') ?>>
            </figure>

            <figure class="hero-v7-pack hero-v7-pack-side" data-parallax="side">
                <span class="pack-aura"></span>
                <img src="<?= e(optimized_image_url('assets/img/hero-signal-pack.png')) ?>" alt="<?= e($heroSignal['name_en']) ?> product pack" loading="eager" fetchpriority="high" decoding="async"<?= image_dimension_attributes('assets/img/hero-signal-pack.webp') ?>>
            </figure>

            <div class="hero-v7-coil-wrap" data-parallax="coil" aria-hidden="true">
                <span class="coil-contact-shadow"></span>
                <img class="hero-v7-coil" src="<?= e(optimized_image_url('assets/img/coil-hero-v7.png')) ?>" alt="">
                <span class="hero-v7-ember hero-v7-ember-one"></span>
                <span class="hero-v7-ember hero-v7-ember-two"></span>
            </div>

            <span class="hero-v7-smoke hero-v7-smoke-one"></span>
            <span class="hero-v7-smoke hero-v7-smoke-two"></span>
            <span class="hero-v7-smoke hero-v7-smoke-three"></span>
            <span class="hero-v7-smoke hero-v7-smoke-four"></span>

            <span class="hero-v7-mosquito hero-v7-m1">🦟</span>
            <span class="hero-v7-mosquito hero-v7-m2">🦟</span>
            <span class="hero-v7-mosquito hero-v7-m3">🦟</span>

            <span class="hero-v7-spark hero-v7-p1"></span>
            <span class="hero-v7-spark hero-v7-p2"></span>
            <span class="hero-v7-spark hero-v7-p3"></span>
            <span class="hero-v7-spark hero-v7-p4"></span>
        </div>
    </div>
</section>

<section id="products" class="section-pad products-section">
    <div class="container">
        <div class="section-head reveal compact-head">
            <span class="eyebrow" data-en="Product Portfolio" data-bn="পণ্যের সংগ্রহ">Product Portfolio</span>
            <h2 data-en="Premium Mosquito Coil Collection" data-bn="প্রিমিয়াম মশার কয়েলের সংগ্রহ">Premium Mosquito Coil Collection</h2>
            <p data-en="Explore our complete range of quality mosquito coils designed for reliable daily protection and broad retail demand." data-bn="নির্ভরযোগ্য দৈনন্দিন সুরক্ষা ও বিস্তৃত বাজার চাহিদার জন্য তৈরি আমাদের সম্পূর্ণ প্রিমিয়াম মশার কয়েলের সংগ্রহ দেখুন।">Explore our complete range of quality mosquito coils designed for reliable daily protection and broad retail demand.</p>
        </div>
        <div class="product-cards showroom-grid">
            <?php foreach ($products as $i => $p): $features = json_features($p['features']); ?>
                <article class="product-card reveal <?= $i===0 ? 'active' : '' ?>" tabindex="0" data-product-id="<?= (int)$p['id'] ?>" style="--accent:<?= e($p['accent_color']) ?>">
                    <div class="card-img"><img src="<?= e(optimized_image_url($p['image'])) ?>" alt="<?= e($p['name_en']) ?>" loading="lazy"></div>
                    <div class="card-body">
                        <span><?= e($p['brand']) ?></span>
                        <h3 data-en="<?= e($p['name_en']) ?>" data-bn="<?= e($p['name_bn']) ?>"><?= e($p['name_en']) ?></h3>
                        <p><b><?= e($p['protection_hours']) ?></b> <span data-en="Hours of Protection" data-bn="ঘণ্টার সুরক্ষা">Hours of Protection</span></p>
                        <div class="feature-tags">
                            <?php foreach(array_slice($features,0,2) as $f): ?><small><?= e($f) ?></small><?php endforeach; ?>
                        </div>
                        <a href="<?= e(product_url($p)) ?>" class="mini-btn" data-en="View Details" data-bn="বিস্তারিত দেখুন">View Details</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="compare" class="section-pad-sm homepage-comparison-v40">
    <div class="container">
        <div class="section-head reveal compact-head">
            <span class="eyebrow" data-en="Compare Products" data-bn="পণ্য তুলনা">Compare Products</span>
            <h2 data-en="Find the right coil for every trade need" data-bn="প্রতিটি ট্রেড প্রয়োজনের জন্য সঠিক কয়েল বেছে নিন">Find the right coil for every trade need</h2>
            <p data-en="Quickly compare protection time, pack focus, smoke comfort and best-use position before opening the full product page." data-bn="পূর্ণ পণ্য পেজ খোলার আগে প্রোটেকশন, প্যাক, ধোঁয়ার ধরন এবং ব্যবহার উপযোগিতা দ্রুত তুলনা করুন।">Quickly compare protection time, pack focus, smoke comfort and best-use position before opening the full product page.</p>
        </div>
        <div class="comparison-table-v40 homepage-compare-table-v40 reveal" role="table" aria-label="A TO Z and SIGNAL product comparison">
            <div class="comparison-row-v40 comparison-head-v40" role="row"><span>Product</span><span>Protection</span><span>Pack</span><span>Smoke / Comfort</span><span>Best For</span><span>Action</span></div>
            <?php foreach($comparisonRows as $row): ?>
                <a class="comparison-row-v40" role="row" href="<?= e(product_url($row)) ?>" style="--accent:<?= e($row['accent_color']) ?>">
                    <span><b data-en="<?= e($row['name_en']) ?>" data-bn="<?= e($row['name_bn']) ?>"><?= e($row['name_en']) ?></b><em><?= e($row['brand']) ?></em></span>
                    <span><?= e($row['protection']) ?></span>
                    <span><?= e($row['pack_size']) ?></span>
                    <span><?= e($row['smoke_type']) ?></span>
                    <span><?= e($row['best_for']) ?></span>
                    <span><?= e($row['highlight']) ?> →</span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="catalogue-strip-v40 reveal">
            <div>
                <b data-en="<?= e(cms_setting($settings,'catalogue_title_en')) ?>" data-bn="<?= e(cms_setting($settings,'catalogue_title_bn')) ?>"><?= e(cms_setting($settings,'catalogue_title_en')) ?></b>
                <span data-en="<?= e(cms_setting($settings,'catalogue_note_en')) ?>" data-bn="<?= e(cms_setting($settings,'catalogue_note_bn')) ?>"><?= e(cms_setting($settings,'catalogue_note_en')) ?></span>
            </div>
            <a class="btn btn-green" href="<?= e($catalogueUrl) ?>" data-catalogue-download data-en="Download Catalogue" data-bn="ক্যাটালগ ডাউনলোড">Download Catalogue</a>
        </div>
    </div>
</section>

<?= render_content_blocks_section('homepage', 'Brand & Trade Highlights') ?>

<section class="experience-section section-pad-sm">
    <div class="container experience-grid">
        <div class="experience-stage reveal" id="productStage" style="--accent:<?= e($active['accent_color']) ?>">
            <div class="protection-ring"></div>
            <div class="stage-product tilt-product">
                <div class="pack-float-wrap">
                    <img id="stageProductImage" src="<?= e(optimized_image_url($active['image'])) ?>" alt="<?= e($active['name_en']) ?>" loading="lazy">
                    <div class="product-reflection"><img id="stageReflection" src="<?= e(optimized_image_url($active['image'])) ?>" alt="" aria-hidden="true"></div>
                </div>
            </div>
            <div class="mini-coil mini-coil-v25" aria-hidden="true">
                <span class="mini-coil-shadow"></span>
                <svg viewBox="0 0 320 220" preserveAspectRatio="xMidYMid meet">
                    <path class="mini-coil-track" d="M168.0 112.0L168.7 112.7L169.9 115.3L169.6 118.5L167.3 121.7L163.0 124.4L157.0 126.0L149.9 126.0L142.7 124.1L136.2 120.2L131.7 114.5L129.9 107.6L131.5 100.1L136.7 93.0L145.5 87.2L157.0 83.6L170.2 82.9L183.7 85.4L196.0 91.2L205.4 100.0L210.5 110.9L210.5 123.0L204.8 134.9L193.7 145.3L177.9 152.7L159.0 156.3L138.7 155.3L119.4 149.4L103.1 139.0L92.0 125.0L87.6 108.7L90.8 91.7L101.6 75.9L119.5 63.0L142.7 54.7L169.1 52.2L196.0 55.9L220.4 65.9L239.5 81.4L251.1 100.9L253.4 122.6L245.9 144.2L228.7 163.3L203.5 177.8L172.5 185.9L138.8 186.5L105.9 179.1L77.4 164.3L56.6 143.3L45.9 118.1L47.0 91.3L60.2 65.7L84.4 44.0L117.6 28.7L156.5 21.7L197.2 24.1L235.3 35.9L266.8 56.1" fill="none"></path>
                    <path class="mini-coil-heat" d="M116.5 182.4L77.4 164.3L51.8 135.2L45.3 100.3L60.2 65.7L94.6 38.1L143.1 23.1L197.2 24.1L246.7 41.8" fill="none"></path>
                </svg>
                <span class="mini-coil-ember"></span>
                <span class="mini-coil-smoke smoke-a"></span>
                <span class="mini-coil-smoke smoke-b"></span>
                <span class="mini-coil-spark spark-a"></span>
                <span class="mini-coil-spark spark-b"></span>
            </div>
        </div>
        <div class="experience-panel reveal">
            <span class="eyebrow" id="stageBadge"><?= e($active['badge']) ?></span>
            <h2 id="stageName" data-en="<?= e($active['name_en']) ?>" data-bn="<?= e($active['name_bn']) ?>"><?= e($active['name_en']) ?></h2>
            <p id="experienceDesc" data-en="<?= e($active['short_description_en']) ?>" data-bn="<?= e($active['short_description_bn']) ?>"><?= e($active['short_description_en']) ?></p>
            <div class="experience-features" id="experienceFeatures">
                <?php foreach(json_features($active['features']) as $f): ?><span><?= e($f) ?></span><?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section id="technology" class="section-pad technology-section">
    <div class="container">
        <div class="section-head reveal compact-head">
            <span class="eyebrow" data-en="Protection Advantages" data-bn="সুরক্ষার বিশেষ সুবিধা">Protection Advantages</span>
            <h2 data-en="Built for Reliable Everyday Protection" data-bn="নির্ভরযোগ্য দৈনন্দিন সুরক্ষার জন্য নির্মিত">Built for Reliable Everyday Protection</h2>
        </div>
        <div class="tech-grid">
            <article class="tech-card reveal"><i>💤</i><h3 data-en="10–12 Hours of Protection" data-bn="১০–১২ ঘণ্টার সুরক্ষা">10–12 Hours of Protection</h3><p data-en="Long-lasting protection designed to cover the night with confidence." data-bn="আত্মবিশ্বাসের সাথে সারা রাত সুরক্ষা দেওয়ার জন্য পরিকল্পিত দীর্ঘস্থায়ী কার্যকারিতা।">Long-lasting protection designed to cover the night with confidence.</p></article>
            <article class="tech-card reveal"><i>🛡</i><h3 data-en="Powerful Mosquito Protection" data-bn="শক্তিশালী মশা সুরক্ষা">Powerful Mosquito Protection</h3><p data-en="A strong formula created for dependable performance in daily use." data-bn="দৈনন্দিন ব্যবহারে নির্ভরযোগ্য পারফরম্যান্সের জন্য তৈরি শক্তিশালী ফর্মুলা।">A strong formula created for dependable performance in daily use.</p></article>
            <article class="tech-card reveal"><i>𖦹</i><h3 data-en="Low Smoke Comfort" data-bn="কম ধোঁয়ার আরাম">Low Smoke Comfort</h3><p data-en="A comfort-focused smoke profile suitable for modern household environments." data-bn="আধুনিক গৃহস্থালী পরিবেশের জন্য উপযোগী আরামদায়ক কম ধোঁয়ার অভিজ্ঞতা।">A comfort-focused smoke profile suitable for modern household environments.</p></article>
            <article class="tech-card reveal"><i>🧪</i><h3 data-en="Premium Quality Formula" data-bn="প্রিমিয়াম মানের ফর্মুলা">Premium Quality Formula</h3><p data-en="Quality ingredients selected to support cleaner burn and stronger performance." data-bn="পরিষ্কার বার্নিং ও উন্নত পারফরম্যান্স নিশ্চিত করতে বাছাইকৃত উপাদান।">Quality ingredients selected to support cleaner burn and stronger performance.</p></article>
            <article class="tech-card reveal"><i>🏡</i><h3 data-en="Suitable for Family Use" data-bn="পারিবারিক ব্যবহারের জন্য উপযোগী">Suitable for Family Use</h3><p data-en="Designed for family environments when used according to instructions." data-bn="নির্দেশনা অনুযায়ী ব্যবহারে পারিবারিক পরিবেশের জন্য উপযোগী।">Designed for family environments when used according to instructions.</p></article>
            <article class="tech-card reveal"><i>♨️</i><h3 data-en="Steady Burn Performance" data-bn="স্থিতিশীল বার্নিং পারফরম্যান্স">Steady Burn Performance</h3><p data-en="Consistent burning behavior from the first spark to the final stage." data-bn="শুরু থেকে শেষ পর্যন্ত স্থিতিশীল বার্নিং পারফরম্যান্স।">Consistent burning behavior from the first spark to the final stage.</p></article>
        </div>
    </div>
</section>

<section id="growth" class="section-pad growth-section-v13 growth-section-v22">
    <div class="container">
        <div class="section-head reveal compact-head growth-head-v13 growth-head-v22">
            <span class="eyebrow" data-en="Corporate Milestones" data-bn="কর্পোরেট মাইলস্টোন">Corporate Milestones</span>
            <h2 data-en="Business Growth Chart & Timeline 2013 – 2026" data-bn="বিজনেস গ্রোথ চার্ট ও টাইমলাইন ২০১৩ – ২০২৬">Business Growth Chart & Timeline 2013 – 2026</h2>
            <p data-en="A cleaner milestone dashboard showing brand development, market expansion, distribution progress and nationwide coverage." data-bn="ব্র্যান্ড উন্নয়ন, বাজার সম্প্রসারণ, ডিস্ট্রিবিউশন অগ্রগতি এবং দেশব্যাপী কাভারেজ দেখানো একটি পরিষ্কার মাইলস্টোন ড্যাশবোর্ড।">A cleaner milestone dashboard showing brand development, market expansion, distribution progress and nationwide coverage.</p>
        </div>

        <div class="growth-board-v13 growth-board-v22 reveal" id="growthBoardV13" aria-label="Business growth chart and timeline from 2013 to 2026">
            <span class="growth-orb-v22 growth-orb-red" aria-hidden="true"></span>
            <span class="growth-orb-v22 growth-orb-green" aria-hidden="true"></span>

            <div class="growth-copy-v13 growth-copy-v22">
                <span data-en="Strategic Growth Journey" data-bn="কৌশলগত প্রবৃদ্ধির যাত্রা">Strategic Growth Journey</span>
                <h3 data-en="From focused launch to stronger nationwide distribution." data-bn="ফোকাসড লঞ্চ থেকে শক্তিশালী দেশব্যাপী ডিস্ট্রিবিউশন।">From focused launch to stronger nationwide distribution.</h3>
                <p data-en="The timeline keeps the story simple: brand foundation, trade development, retail growth, portfolio strength and the 2026 target of full 64-district coverage." data-bn="এই টাইমলাইন গল্পটিকে সহজ রাখে: ব্র্যান্ডের ভিত্তি, ট্রেড উন্নয়ন, রিটেইল গ্রোথ, পোর্টফোলিও শক্তি এবং ২০২৬ সালের ৬৪ জেলা কাভারেজের লক্ষ্য।">The timeline keeps the story simple: brand foundation, trade development, retail growth, portfolio strength and the 2026 target of full 64-district coverage.</p>
                <div class="growth-kpis-v22" aria-label="Growth summary">
                    <span><b>2013</b><em data-en="Started" data-bn="শুরু">Started</em></span>
                    <span><b>64</b><em data-en="District Target" data-bn="জেলার লক্ষ্য">District Target</em></span>
                    <span><b>2</b><em data-en="Core Brands" data-bn="মূল ব্র্যান্ড">Core Brands</em></span>
                </div>
            </div>

            <div class="growth-bg-bars growth-bg-bars-v22" aria-hidden="true">
                <span style="--h:12%"></span><span style="--h:18%"></span><span style="--h:28%"></span><span style="--h:40%"></span><span style="--h:52%"></span><span style="--h:66%"></span><span style="--h:80%"></span><span style="--h:96%"></span>
            </div>

            <div class="growth-desktop-v13 growth-desktop-v22" aria-hidden="false">
                <svg class="growth-curve-v13 growth-curve-v22" viewBox="0 0 1120 460" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="growthV22Line" x1="0" y1="1" x2="1" y2="0">
                            <stop offset="0%" stop-color="#b095d4"/>
                            <stop offset="30%" stop-color="#4ecdc4"/>
                            <stop offset="62%" stop-color="#ffb347"/>
                            <stop offset="100%" stop-color="#ff6b6b"/>
                        </linearGradient>
                    </defs>
                    <path class="growth-line-shadow-v13" d="M70 365 C185 310 270 302 362 238 C462 168 575 170 670 118 C790 52 905 72 1048 36"/>
                    <path class="growth-line-v13" d="M70 365 C185 310 270 302 362 238 C462 168 575 170 670 118 C790 52 905 72 1048 36"/>
                    <circle class="growth-dot-v13" cx="70" cy="365" r="8"/><circle class="growth-dot-v13" cx="252" cy="295" r="8"/><circle class="growth-dot-v13" cx="430" cy="198" r="8"/><circle class="growth-dot-v13" cx="642" cy="132" r="8"/><circle class="growth-dot-v13" cx="826" cy="69" r="8"/><circle class="growth-dot-v13 future" cx="1048" cy="36" r="10"/>
                </svg>

                <article class="growth-mark-v13 growth-mark-v22 m13 purple" style="--x:7%;--y:80%;--bar:18%;"><div class="growth-icon">👥</div><strong>2013</strong><span data-en="Start" data-bn="শুরু">Start</span><em data-en="Brand foundation" data-bn="ব্র্যান্ডের ভিত্তি">Brand foundation</em></article>
                <article class="growth-mark-v13 growth-mark-v22 m16 mint" style="--x:24%;--y:65%;--bar:35%;"><div class="growth-icon">🤝</div><strong>2016</strong><span>35%</span><em data-en="Trade network" data-bn="ট্রেড নেটওয়ার্ক">Trade network</em></article>
                <article class="growth-mark-v13 growth-mark-v22 m19 cyan" style="--x:42%;--y:44%;--bar:55%;"><div class="growth-icon">🏬</div><strong>2019</strong><span>55%</span><em data-en="Retail expansion" data-bn="রিটেইল সম্প্রসারণ">Retail expansion</em></article>
                <article class="growth-mark-v13 growth-mark-v22 m22 orange" style="--x:61%;--y:30%;--bar:75%;"><div class="growth-icon">৳</div><strong>2022</strong><span>75%</span><em data-en="Sales growth" data-bn="বিক্রয় প্রবৃদ্ধি">Sales growth</em></article>
                <article class="growth-mark-v13 growth-mark-v22 m24 rose" style="--x:78%;--y:18%;--bar:88%;"><div class="growth-icon">🛡</div><strong>2024</strong><span>88%</span><em data-en="Portfolio strength" data-bn="পোর্টফোলিও শক্তি">Portfolio strength</em></article>
                <article class="growth-mark-v13 growth-mark-v22 m26 coral future" style="--x:94%;--y:10%;--bar:100%;"><div class="future-ring"></div><div class="growth-icon">★</div><strong>2026</strong><span>100%</span><em data-en="64 district coverage" data-bn="৬৪ জেলায় কাভারেজ">64 district coverage</em></article>
            </div>

            <div class="growth-mobile-v13 growth-mobile-v22" aria-label="Mobile business growth timeline">
                <article class="purple"><strong>2013</strong><div><span data-en="Brand foundation" data-bn="ব্র্যান্ডের ভিত্তি">Brand foundation</span><p data-en="Initial market entry with a focused protection offering." data-bn="নির্দিষ্ট সুরক্ষা পণ্য নিয়ে প্রাথমিক বাজারে প্রবেশ।">Initial market entry with a focused protection offering.</p></div></article>
                <article class="mint"><strong>2016</strong><div><span data-en="35% trade network" data-bn="৩৫% ট্রেড নেটওয়ার্ক">35% trade network</span><p data-en="A stronger distributor and channel network supports wider market reach." data-bn="শক্তিশালী ডিস্ট্রিবিউটর ও চ্যানেল নেটওয়ার্ক বাজার সম্প্রসারণে সহায়তা করে।">A stronger distributor and channel network supports wider market reach.</p></div></article>
                <article class="cyan"><strong>2019</strong><div><span data-en="55% retail expansion" data-bn="৫৫% রিটেইল সম্প্রসারণ">55% retail expansion</span><p data-en="Expanded retail visibility improves availability and brand presence." data-bn="রিটেইল দৃশ্যমানতা বাড়িয়ে প্রাপ্যতা ও ব্র্যান্ড উপস্থিতি শক্তিশালী করে।">Expanded retail visibility improves availability and brand presence.</p></div></article>
                <article class="orange"><strong>2022</strong><div><span data-en="75% sales growth" data-bn="৭৫% বিক্রয় প্রবৃদ্ধি">75% sales growth</span><p data-en="Consistent sales momentum reflects stronger consumer acceptance." data-bn="ধারাবাহিক বিক্রয় গতি ভোক্তা গ্রহণযোগ্যতার শক্ত ভিত্তিকে প্রতিফলিত করে।">Consistent sales momentum reflects stronger consumer acceptance.</p></div></article>
                <article class="rose"><strong>2024</strong><div><span data-en="88% portfolio strength" data-bn="৮৮% পোর্টফোলিও শক্তি">88% portfolio strength</span><p data-en="A stronger brand portfolio adds modern market appeal and depth." data-bn="শক্তিশালী ব্র্যান্ড পোর্টফোলিও বাজারে আধুনিকতা ও গভীরতা যোগ করে।">A stronger brand portfolio adds modern market appeal and depth.</p></div></article>
                <article class="coral active"><strong>2026</strong><div><span data-en="100% 64 district coverage" data-bn="১০০% ৬৪ জেলা কাভারেজ">100% 64 district coverage</span><p data-en="A nationwide distribution milestone built on trust, reach and execution strength." data-bn="আস্থা, বিস্তার ও কার্যকর বাস্তবায়নের ভিত্তিতে অর্জিত একটি জাতীয় ডিস্ট্রিবিউশন মাইলস্টোন।">A nationwide distribution milestone built on trust, reach and execution strength.</p></div></article>
            </div>
        </div>
    </div>
</section>

<section id="story" class="section-pad story-section-v13 story-section-v22">
    <div class="container">
        <div class="section-head reveal compact-head brand-head-v13 brand-head-v22">
            <span class="eyebrow" data-en="About Our Brands" data-bn="আমাদের ব্র্যান্ড সম্পর্কে">About Our Brands</span>
            <h2 data-en="Two Trusted Brands. One Standard of Protection." data-bn="দুই বিশ্বস্ত ব্র্যান্ড। সুরক্ষার এক মানদণ্ড।">Two Trusted Brands. One Standard of Protection.</h2>
            <p data-en="A TO Z and SIGNAL serve Bangladesh with dependable mosquito coil solutions, strong retail presence and trusted household protection." data-bn="A TO Z এবং SIGNAL বাংলাদেশজুড়ে নির্ভরযোগ্য মশার কয়েল, শক্তিশালী বাজার উপস্থিতি এবং বিশ্বস্ত পারিবারিক সুরক্ষা নিশ্চিত করছে।">A TO Z and SIGNAL serve Bangladesh with dependable mosquito coil solutions, strong retail presence and trusted household protection.</p>
        </div>

        <div class="brand-scene-v13 brand-scene-v22 reveal">
            <article class="brand-info-v13 brand-panel-v22 atoz-info">
                <div class="brand-panel-logo-v22"><img src="<?= e(asset_url('assets/img/logo-atoz.webp')) ?>" alt="A TO Z logo" loading="lazy"><span data-en="Premium coil protection" data-bn="প্রিমিয়াম কয়েল সুরক্ষা">Premium coil protection</span></div>
                <h3><span class="brand-word atoz">A TO Z</span></h3>
                <ul><li data-en="Manufactured by Nabiad Distribution Limited" data-bn="উৎপাদন ও বাজারজাতকরণ: নাবীয়াদ ডিস্ট্রিবিউশন লিমিটেড">Manufactured by Nabiad Distribution Limited</li><li data-en="Premium mosquito coil for dependable daily protection" data-bn="নির্ভরযোগ্য দৈনন্দিন সুরক্ষার জন্য প্রিমিয়াম মশার কয়েল">Premium mosquito coil for dependable daily protection</li><li data-en="Strong burning performance with longer-lasting coverage" data-bn="দীর্ঘস্থায়ী সুরক্ষার জন্য শক্তিশালী ও স্থিতিশীল বার্নিং পারফরম্যান্স">Strong burning performance with longer-lasting coverage</li><li data-en="Trusted across Bangladesh for steady household demand" data-bn="বাংলাদেশজুড়ে ধারাবাহিক গৃহস্থালী চাহিদার বিশ্বস্ত নাম">Trusted across Bangladesh for steady household demand</li></ul>
                <p class="brand-maker" data-en='"A TO Z" branded mosquito coils are manufactured by Nabiad Distribution Limited.' data-bn='"এ টু জেড" ব্র্যান্ডের মশার কয়েল প্রস্তুতকারক নাবীয়াদ ডিস্ট্রিবিউশন লিমিটেড।'>"A TO Z" branded mosquito coils are manufactured by Nabiad Distribution Limited.</p>
                <a href="<?= e(home_url('products')) ?>" class="btn btn-red" data-en="Explore A TO Z →" data-bn="A TO Z দেখুন →">Explore A TO Z →</a>
            </article>

            <div class="brand-product-card-v13 brand-product-card-v22 atoz-product">
                <span class="brand-product-glow-v22"></span>
                <img src="<?= e(optimized_image_url($heroAtoz['image'])) ?>" alt="<?= e($heroAtoz['name_en']) ?>" loading="lazy">
                <div class="brand-coil-v13 brand-coil-v22"><span></span><i></i></div>
            </div>

            <div class="brand-battle-v25" aria-hidden="true">
                <span class="brand-battle-halo-v25"></span>
                <span class="brand-battle-badge-v25 battle-atoz"><img src="<?= e(asset_url('assets/img/logo-atoz.webp')) ?>" alt=""></span>
                <span class="brand-battle-badge-v25 battle-signal"><img src="<?= e(asset_url('assets/img/logo-signal.webp')) ?>" alt=""></span>
                <span class="brand-beam-v25 beam-atoz beam-atoz-one"></span>
                <span class="brand-beam-v25 beam-atoz beam-atoz-two"></span>
                <span class="brand-beam-v25 beam-signal beam-signal-one"></span>
                <span class="brand-beam-v25 beam-signal beam-signal-two"></span>
                <span class="brand-power-spark-v25 spark-one"></span>
                <span class="brand-power-spark-v25 spark-two"></span>
                <span class="brand-power-spark-v25 spark-three"></span>
                <span class="brand-power-spark-v25 spark-four"></span>
                <span class="brand-mosquito-v25 scared mosquito-one">🦟</span>
                <span class="brand-mosquito-v25 scared mosquito-two">🦟</span>
                <span class="brand-mosquito-v25 dead mosquito-three">🦟</span>
                <span class="brand-mosquito-v25 dead mosquito-four">🦟</span>
            </div>

            <div class="brand-product-card-v13 brand-product-card-v22 signal-product">
                <span class="brand-product-glow-v22"></span>
                <img src="<?= e(optimized_image_url($heroSignal['image'])) ?>" alt="<?= e($heroSignal['name_en']) ?>" loading="lazy">
                <div class="brand-coil-v13 brand-coil-v22"><span></span><i></i></div>
            </div>

            <article class="brand-info-v13 brand-panel-v22 signal-info">
                <div class="brand-panel-logo-v22 signal-logo-panel"><img src="<?= e(asset_url('assets/img/logo-signal.webp')) ?>" alt="SIGNAL logo" loading="lazy"><span data-en="Smokeless Technology" data-bn="ধোঁয়াবিহীন প্রযুক্তি">Smokeless Technology</span></div>
                <h3><span class="brand-word signal">SIGNAL</span></h3>
                <ul><li data-en="Manufactured by NZ Corporation" data-bn="উৎপাদন ও বাজারজাতকরণ: এন জেড কর্পোরেশন">Manufactured by NZ Corporation</li><li data-en="Advanced low-smoke concept for modern household use" data-bn="আধুনিক গৃহস্থালী ব্যবহারের জন্য উন্নত কম-ধোঁয়া কনসেপ্ট">Advanced low-smoke concept for modern household use</li><li data-en="Balanced protection with pleasant user comfort" data-bn="আরামদায়ক ব্যবহারের অভিজ্ঞতার সাথে সুষম সুরক্ষা">Balanced protection with pleasant user comfort</li><li data-en="Reliable mosquito protection with strong shelf appeal" data-bn="শক্তিশালী বাজার উপস্থিতিসহ নির্ভরযোগ্য মশা সুরক্ষা">Reliable mosquito protection with strong shelf appeal</li></ul>
                <p class="brand-maker" data-en='"SIGNAL" branded mosquito coils are manufactured by NZ Corporation.' data-bn='"সিগনাল" ব্র্যান্ডের মশার কয়েল প্রস্তুতকারক এন জেড কর্পোরেশন।'>"SIGNAL" branded mosquito coils are manufactured by NZ Corporation.</p>
                <a href="<?= e(home_url('products')) ?>" class="btn btn-green" data-en="Explore SIGNAL →" data-bn="SIGNAL দেখুন →">Explore SIGNAL →</a>
            </article>
        </div>
    </div>
</section>

<section class="stats-strip section-pad-sm">
    <div class="container stats-grid">
        <div class="reveal"><i>👥</i><strong><span class="counter" data-target="20">0</span>M+</strong><span data-en="Happy Consumers" data-bn="খুশি গ্রাহক">Happy Consumers</span></div>
        <div class="reveal"><i>🏬</i><strong><span class="counter" data-target="1">0</span>M+</strong><span data-en="Retail Outlets" data-bn="রিটেইল আউটলেট">Retail Outlets</span></div>
        <div class="reveal"><i>📍</i><strong><span class="counter" data-target="64">0</span></strong><span data-en="Districts Covered" data-bn="জেলা কাভারেজ">Districts Covered</span></div>
        <div class="reveal"><i>🛡</i><strong><span class="counter" data-target="20">0</span>+</strong><span data-en="Years of Trust" data-bn="বছরের বিশ্বাস">Years of Trust</span></div>
    </div>
</section>

<section class="distributor-cta section-pad-sm" id="distributorCta">
    <div class="container distributor-cta-card reveal">
        <div>
            <span class="eyebrow" data-en="Distributor Opportunity" data-bn="ডিস্ট্রিবিউটর সুযোগ">Distributor Opportunity</span>
            <h2 data-en="Let's Grow with A TO Z & SIGNAL" data-bn="A TO Z ও SIGNAL-এ, চালুন সবাই একসাথে।">Let's Grow with A TO Z & SIGNAL</h2>
            <p data-en="Partner with trusted mosquito coil brands backed by reliable supply, trade support and nationwide market demand." data-bn="নির্ভরযোগ্য সরবরাহ, ট্রেড সাপোর্ট এবং জাতীয় বাজার চাহিদাসম্পন্ন বিশ্বস্ত মশার কয়েল ব্র্যান্ডের সাথে পার্টনার হোন।">Partner with trusted mosquito coil brands backed by reliable supply, trade support and nationwide demand.</p>
        </div>
        <div class="cta-benefits">
            <span data-en="Fast enquiry response" data-bn="দ্রুত ইনকোয়ারি রেসপন্স">Fast enquiry response</span>
            <span data-en="Dealer & wholesale support" data-bn="ডিলার ও পাইকারি সাপোর্ট">Dealer & wholesale support</span>
            <span data-en="64 district coverage focus" data-bn="৬৪ জেলায় কাভারেজ ফোকাস">64 district coverage focus</span>
        </div>
        <div class="cta-actions">
            <a class="btn btn-red" href="#distributorForm" data-en="Apply as Distributor" data-bn="ডিস্ট্রিবিউটর হিসেবে আবেদন করুন">Apply as Distributor</a>
            <a class="btn btn-green" href="https://wa.me/<?= e($settings['whatsapp_number']) ?>" target="_blank" rel="noopener" data-en="WhatsApp Sales Team" data-bn="সেলস টিমে WhatsApp করুন">WhatsApp Sales Team</a>
        </div>
    </div>
</section>

<section id="network" class="section-pad network-section">
    <div class="container network-grid">
        <div class="network-copy reveal">
            <span class="eyebrow" data-en="Distributor Network" data-bn="ডিস্ট্রিবিউটর নেটওয়ার্ক">Distributor Network</span>
            <h2 data-en="Across All 64 Districts Coverage" data-bn="দেশের ৬৪ জেলায় সরবরাহ সুবিধা">Across All 64 Districts Coverage</h2>
            <p data-en="Connect with our nationwide distribution network, explore district coverage and submit your enquiry directly to the team." data-bn="আমাদের জাতীয় ডিস্ট্রিবিউশন নেটওয়ার্কের সাথে যুক্ত হোন, জেলা কাভারেজ দেখুন এবং সরাসরি টিমের কাছে আপনার ইনকোয়ারি পাঠান।">Connect with our nationwide distribution network, explore district coverage and submit your enquiry directly to the team.</p>
            <ul class="check-list"><li data-en="Competitive trade margins" data-bn="প্রতিযোগিতামূলক ট্রেড মার্জিন">Competitive trade margins</li><li data-en="Brand and marketing support" data-bn="ব্র্যান্ড ও মার্কেটিং সাপোর্ট">Brand and marketing support</li><li data-en="Reliable supply and timely delivery" data-bn="নির্ভরযোগ্য সরবরাহ ও সময়মতো ডেলিভারি">Reliable supply and timely delivery</li><li data-en="Product guidance and trade assistance" data-bn="পণ্য নির্দেশনা ও ট্রেড সহায়তা">Product guidance and trade assistance</li><li data-en="Long-term partnership opportunities" data-bn="দীর্ঘমেয়াদি পার্টনারশিপের সুযোগ">Long-term partnership opportunities</li></ul>
            <a class="btn btn-red" href="#distributorForm" data-en="Apply Now" data-bn="এখন আবেদন করুন">Apply Now</a>
        </div>
        <div class="map-card reveal" aria-label="Bangladesh 64 district coverage map">
            <?php $bdPath = 'M 77.4 296.5 L 72.1 287.6 L 71.7 281.8 L 74.4 278.7 L 72.8 271.0 L 75.4 268.1 L 70.8 260.8 L 70.4 250.3 L 66.9 245.8 L 69.0 229.3 L 63.5 222.7 L 63.9 216.0 L 69.8 209.4 L 56.3 207.0 L 60.0 193.6 L 57.7 195.1 L 48.3 184.9 L 50.3 173.3 L 57.1 170.2 L 57.9 164.0 L 55.1 160.7 L 57.2 152.2 L 54.7 148.2 L 44.9 148.0 L 25.9 137.6 L 21.8 128.3 L 28.3 118.7 L 26.6 114.9 L 31.1 113.4 L 36.8 117.9 L 39.7 114.0 L 43.1 106.1 L 42.5 100.2 L 60.6 101.4 L 62.2 99.3 L 67.5 101.4 L 71.0 94.6 L 62.6 90.5 L 60.3 82.4 L 54.9 84.7 L 47.4 82.7 L 42.5 77.8 L 42.3 73.8 L 33.8 66.3 L 27.3 67.6 L 25.2 64.4 L 24.4 60.0 L 28.9 53.5 L 28.9 47.0 L 37.4 43.2 L 37.7 39.7 L 42.8 35.0 L 46.3 35.5 L 44.2 29.9 L 36.8 29.2 L 40.4 20.5 L 41.6 25.0 L 50.9 29.7 L 57.2 36.3 L 53.8 40.8 L 58.7 38.8 L 62.8 42.6 L 65.9 39.7 L 73.1 42.2 L 72.1 38.7 L 66.3 35.1 L 69.0 30.6 L 76.9 36.8 L 75.0 37.8 L 78.2 47.8 L 89.3 55.2 L 91.3 53.9 L 99.6 57.2 L 102.9 51.5 L 100.6 46.6 L 105.0 42.9 L 105.0 46.4 L 108.1 46.7 L 110.1 53.1 L 114.1 56.0 L 112.3 58.3 L 114.6 58.9 L 111.6 65.1 L 114.5 75.2 L 112.0 89.9 L 113.5 94.6 L 117.2 93.4 L 143.7 102.4 L 185.5 99.1 L 196.9 102.9 L 202.4 101.0 L 205.4 103.5 L 225.4 100.2 L 240.2 106.1 L 248.6 116.6 L 242.8 118.3 L 235.9 116.2 L 236.3 123.0 L 233.0 134.5 L 230.0 137.3 L 230.2 142.8 L 222.0 144.8 L 222.7 148.1 L 219.4 147.2 L 218.0 157.3 L 210.9 152.5 L 209.7 157.5 L 205.8 153.3 L 203.5 160.1 L 191.4 160.1 L 191.0 165.5 L 186.1 167.3 L 183.7 178.7 L 180.3 180.5 L 182.5 183.5 L 180.5 185.5 L 182.9 192.6 L 188.4 200.8 L 189.7 215.1 L 192.8 217.0 L 191.6 209.4 L 193.8 206.3 L 203.6 224.0 L 214.0 216.0 L 211.5 205.7 L 215.4 198.2 L 221.5 193.5 L 220.7 180.5 L 224.8 185.3 L 231.3 180.2 L 234.1 184.9 L 236.1 181.0 L 238.0 182.5 L 237.4 187.5 L 242.9 205.5 L 241.0 208.5 L 242.4 224.1 L 245.9 227.0 L 249.5 236.2 L 254.1 267.0 L 251.9 268.7 L 257.4 314.8 L 253.6 317.3 L 251.5 310.6 L 245.0 310.5 L 241.8 305.1 L 233.2 312.7 L 233.5 323.4 L 236.7 327.8 L 241.2 346.3 L 226.0 321.6 L 226.6 315.0 L 220.8 306.4 L 225.8 302.5 L 223.8 302.9 L 223.0 299.6 L 224.6 294.0 L 221.3 293.9 L 222.0 303.0 L 217.8 302.0 L 217.8 294.7 L 222.2 287.6 L 217.7 288.6 L 220.1 285.4 L 215.6 268.2 L 217.5 268.9 L 215.6 267.7 L 215.2 269.8 L 214.0 266.6 L 215.1 261.5 L 212.0 262.3 L 208.6 249.2 L 195.1 233.9 L 186.4 234.2 L 185.6 236.6 L 177.6 234.3 L 172.6 238.1 L 175.1 242.9 L 171.2 244.6 L 161.1 234.1 L 156.4 233.9 L 157.4 241.1 L 165.5 249.1 L 163.7 267.5 L 153.2 276.0 L 154.5 272.2 L 152.7 274.9 L 151.9 273.0 L 155.8 261.1 L 155.6 249.6 L 152.7 243.4 L 152.6 245.1 L 148.6 242.6 L 152.3 259.7 L 148.4 265.8 L 146.3 265.4 L 143.9 271.7 L 141.0 269.8 L 143.3 265.1 L 141.6 266.5 L 135.4 283.5 L 131.3 286.9 L 125.7 284.6 L 128.8 277.7 L 125.4 283.5 L 122.4 281.9 L 126.4 273.0 L 132.2 269.1 L 129.1 269.1 L 124.3 277.0 L 122.5 276.6 L 120.8 273.9 L 125.8 267.1 L 122.6 265.2 L 124.7 266.9 L 118.1 279.7 L 114.8 261.9 L 116.4 254.5 L 113.2 262.8 L 115.4 269.9 L 112.2 272.5 L 115.1 281.7 L 110.1 285.7 L 108.3 283.4 L 108.3 285.4 L 104.4 284.4 L 105.6 279.8 L 103.7 281.3 L 103.2 278.3 L 99.5 279.1 L 99.9 271.8 L 97.4 276.6 L 97.1 271.3 L 95.9 274.5 L 94.5 273.6 L 96.2 281.2 L 93.6 281.2 L 92.0 277.6 L 94.6 281.6 L 92.1 284.5 L 93.8 289.2 L 91.2 291.7 L 88.7 290.1 L 89.0 279.0 L 86.0 286.3 L 84.2 285.7 L 85.3 289.7 L 81.6 289.9 L 80.5 284.8 L 78.4 287.5 L 75.6 286.4 L 75.2 273.0 L 72.6 271.9 L 74.1 279.2 L 71.8 280.1 L 71.4 277.3 L 69.8 282.0 L 72.1 283.6 L 70.6 286.6 L 75.5 296.0 Z'; ?>
            <div class="bd-map-wrap">
                <svg class="bd-map-svg" viewBox="0 0 280 370" role="img" aria-label="Bangladesh 64 district coverage map">
                    <defs>
                        <linearGradient id="bdFill" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#174b2c"/>
                            <stop offset="55%" stop-color="#246b3c"/>
                            <stop offset="100%" stop-color="#0d301d"/>
                        </linearGradient>
                        <filter id="mapGlow" x="-50%" y="-50%" width="200%" height="200%">
                            <feGaussianBlur stdDeviation="4.5" result="blur"/>
                            <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                        </filter>
                        <clipPath id="bdClip"><path d="<?= $bdPath ?>"/></clipPath>
                    </defs>
                    <g filter="url(#mapGlow)">
                        <path class="bd-map-main" d="<?= $bdPath ?>" fill="url(#bdFill)" stroke="#f5a623" stroke-width="2.8" stroke-linejoin="round" opacity=".95"/>
                        <ellipse cx="154" cy="292" rx="7" ry="14" transform="rotate(-18 154 292)" fill="url(#bdFill)" stroke="#f5a623" stroke-width="1.7" opacity=".9"/>
                        <ellipse cx="174" cy="286" rx="8" ry="17" transform="rotate(8 174 286)" fill="url(#bdFill)" stroke="#f5a623" stroke-width="1.7" opacity=".9"/>
                        <ellipse cx="190" cy="276" rx="5" ry="12" transform="rotate(18 190 276)" fill="url(#bdFill)" stroke="#f5a623" stroke-width="1.7" opacity=".9"/>
                        <ellipse cx="202" cy="267" rx="4.5" ry="10" transform="rotate(22 202 267)" fill="url(#bdFill)" stroke="#f5a623" stroke-width="1.7" opacity=".9"/>
                    </g>
                    <path class="bd-map-inner-outline" d="<?= $bdPath ?>" fill="none" stroke="rgba(255,255,255,.38)" stroke-width=".7" stroke-linejoin="round"/>
                    <g clip-path="url(#bdClip)" class="bd-map-lines" opacity=".34">
                        <path d="M48 126 C86 96 116 118 144 90 C176 62 208 74 238 52"/>
                        <path d="M42 174 C86 148 116 170 154 144 C198 114 214 134 252 112"/>
                        <path d="M48 224 C92 204 126 220 164 196 C204 170 226 182 252 158"/>
                        <path d="M78 280 C112 252 142 278 184 246 C220 218 232 228 252 210"/>
                    </g>
                    <circle cx="147" cy="180" r="68" class="coverage-wave" fill="none" stroke="#f5a623" stroke-width="1" opacity=".34"/>
                    <circle cx="147" cy="180" r="96" class="coverage-wave wave-two" fill="none" stroke="#2bbf5a" stroke-width="1" opacity=".22"/>
                    <path class="connection-path" d="M147 180 Q80 240 110 240"/>
                    <path class="connection-path green" d="M147 180 Q264 265 234 265"/>
                    <path class="connection-path" d="M147 180 Q98 267 128 267"/>
                    <path class="connection-path green" d="M147 180 Q170 237 140 237"/>
                    <path class="connection-path" d="M147 180 Q119 119 89 119"/>
                    <path class="connection-path green" d="M147 180 Q148 168 178 168"/>
                    <path class="connection-path" d="M147 180 Q194 307 224 307"/>
                    <path class="connection-path green" d="M147 180 Q82 76 52 76"/>
                    <?php
                $mapPins = [
                    ['Bagerhat', 110.7143, 240.1613, 3.7],
                    ['Bandarban', 234.4783, 264.9839, 2.35],
                    ['Barguna', 128.0311, 267.1903, 2.35],
                    ['Barishal', 139.7453, 237.4032, 2.35],
                    ['Bhola', 154.5155, 237.9548, 2.35],
                    ['Bogura', 89.3230, 118.8065, 2.35],
                    ['Brahmanbaria', 177.9441, 167.9000, 2.35],
                    ['Chandpur', 155.5342, 208.1677, 2.35],
                    ['Chattogram', 212.0683, 256.1581, 3.7],
                    ['Chuadanga', 63.3478, 185.5516, 3.7],
                    ['Cox\'s Bazar', 223.7826, 307.4581, 2.35],
                    ['Cumilla', 181.5093, 195.4806, 2.35],
                    ['Dhaka', 142.2919, 176.1742, 3.7],
                    ['Dinajpur', 51.6335, 75.7806, 2.35],
                    ['Faridpur', 113.2609, 187.2065, 2.35],
                    ['Feni', 192.2050, 219.7516, 2.35],
                    ['Gaibandha', 97.9814, 92.3290, 2.35],
                    ['Gazipur', 142.8012, 165.6935, 2.35],
                    ['Gopalganj', 112.7516, 220.8548, 3.7],
                    ['Habiganj', 193.7329, 144.7323, 2.35],
                    ['Jamalpur', 135.6708, 113.8419, 2.35],
                    ['Jashore', 81.1739, 211.4774, 2.35],
                    ['Jhalokathi', 131.5963, 240.7129, 2.35],
                    ['Jhenaidah', 79.6460, 191.0677, 2.35],
                    ['Joypurhat', 71.4969, 105.0161, 2.35],
                    ['Khagrachhari', 221.7453, 214.7871, 2.35],
                    ['Khulna', 98.4907, 230.7839, 3.7],
                    ['Kishoreganj', 161.1366, 141.9742, 3.7],
                    ['Kurigram', 103.5839, 66.4032, 2.35],
                    ['Kushtia', 76.5901, 170.6581, 2.35],
                    ['Lakshmipur', 163.6832, 224.1645, 2.35],
                    ['Lalmonirhat', 93.3975, 59.7839, 2.35],
                    ['Madaripur', 131.5963, 211.4774, 2.35],
                    ['Magura', 91.8696, 193.8258, 2.35],
                    ['Manikganj', 121.4099, 173.4161, 2.35],
                    ['Meherpur', 51.6335, 178.9323, 2.35],
                    ['Moulvibazar', 211.5590, 139.2161, 3.7],
                    ['Munshiganj', 148.4037, 191.0677, 2.35],
                    ['Mymensingh', 141.7826, 124.3226, 2.35],
                    ['Naogaon', 66.9130, 121.5645, 2.35],
                    ['Narail', 95.9441, 211.4774, 2.35],
                    ['Narayanganj', 146.8758, 186.6548, 2.35],
                    ['Narsingdi', 158.0807, 170.1065, 2.35],
                    ['Natore', 70.4783, 142.5258, 2.35],
                    ['Netrokona', 158.5901, 117.1516, 2.35],
                    ['Nilphamari', 63.3478, 58.1290, 3.7],
                    ['Noakhali', 177.4348, 230.7839, 2.35],
                    ['Pabna', 83.2112, 165.6935, 2.35],
                    ['Panchagarh', 47.5590, 36.6161, 2.35],
                    ['Patuakhali', 138.2174, 256.1581, 2.35],
                    ['Pirojpur', 119.8820, 244.0226, 2.35],
                    ['Rajbari', 103.5839, 179.4839, 2.35],
                    ['Rajshahi', 50.1056, 145.2839, 3.7],
                    ['Rangamati', 233.4596, 239.6097, 2.35],
                    ['Rangpur', 83.2112, 69.1613, 3.7],
                    ['Satkhira', 74.5528, 236.3000, 2.35],
                    ['Shariatpur', 143.3106, 207.6161, 2.35],
                    ['Sherpur', 122.4286, 109.4290, 2.35],
                    ['Sirajganj', 106.1304, 140.8710, 2.35],
                    ['Sunamganj', 192.2050, 106.6710, 2.35],
                    ['Sylhet', 216.6522, 116.6000, 3.7],
                    ['Tangail', 117.3354, 151.9032, 2.35],
                    ['Thakurgaon', 43.4845, 53.7161, 2.35],
                    ['Chapainawabganj', 33.2981, 132.5968, 3.7],
                ];
                foreach ($mapPins as $n => $pin):
                    [$districtName, $cx, $cy, $r] = $pin;
                    $isMajor = $r > 3;
                ?>
                    <g class="map-pin" style="--delay:<?= ($n % 12) * .16 ?>s;">
                        <title><?= e($districtName) ?></title>
                        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $isMajor ? 7.7 : 5.2 ?>" fill="none" stroke="<?= $isMajor ? '#ff6b35' : '#f5a623' ?>" stroke-width=".85" opacity=".42"/>
                        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="<?= $isMajor ? '#ff6b35' : '#f5a623' ?>"/>
                    </g>
                <?php endforeach; ?>
                </svg>
                <div class="coverage-badge"><span data-en="BD All 64 Districts" data-bn="বাংলাদেশের সব ৬৪ জেলা">BD All 64 Districts</span></div>
            </div>
        </div>
        <form class="distributor-form lead-enquiry-form reveal" id="distributorForm" method="post" action="api/enquiry.php">
            <?= csrf_field() ?>
            <h3 data-en="Distributor Enquiry Form" data-bn="ডিস্ট্রিবিউটর ইনকোয়ারি ফর্ম">Distributor Enquiry Form</h3>
            <input type="hidden" name="lead_source" value="Website">
            <label class="lead-hp-field" aria-hidden="true" tabindex="-1"><span>Leave this field blank</span><input name="website" tabindex="-1" autocomplete="off"></label>
            <label><span data-en="Your Name" data-bn="আপনার নাম">Your Name</span><input name="name" required maxlength="120" autocomplete="name" placeholder="Enter your name" data-placeholder-en="Enter your name" data-placeholder-bn="আপনার নাম লিখুন"></label>
            <label><span data-en="Phone Number" data-bn="ফোন নম্বর">Phone Number</span><input name="phone" required maxlength="30" autocomplete="tel" inputmode="tel" placeholder="Enter your phone number" data-placeholder-en="Enter your phone number" data-placeholder-bn="আপনার ফোন নম্বর লিখুন"></label>
            <label><span data-en="Shop / Company Name" data-bn="দোকান / কোম্পানির নাম">Shop / Company Name</span><input name="company_name" maxlength="160" autocomplete="organization" placeholder="Example: Rahim Traders" data-placeholder-en="Example: Rahim Traders" data-placeholder-bn="যেমন: রহিম ট্রেডার্স"></label>
            <label><span data-en="Interested Brand" data-bn="যে ব্র্যান্ডে আগ্রহী">Interested Brand</span><select name="interested_brand" required><option>Both</option><option>A TO Z</option><option>SIGNAL</option></select></label>
            <label><span data-en="District" data-bn="জেলা">District</span><select name="district" required><option value="">Select your district</option><?php foreach($districts as $d): ?><option value="<?= e($d) ?>"><?= e($d) ?></option><?php endforeach; ?></select></label>
            <label><span data-en="Business Type" data-bn="ব্যবসার ধরন">Business Type</span><select name="business_type" required><option>Retailer</option><option>Wholesaler</option><option>Distributor</option><option>Super Shop</option><option>Other</option></select></label>
            <label class="full"><span data-en="Business Address / Area" data-bn="ব্যবসার ঠিকানা / এলাকা">Business Address / Area</span><input name="business_address" maxlength="220" autocomplete="street-address" placeholder="Market name, area or address" data-placeholder-en="Market name, area or address" data-placeholder-bn="মার্কেটের নাম, এলাকা বা ঠিকানা"></label>
            <label class="full"><span data-en="Message" data-bn="মেসেজ">Message</span><textarea name="message" rows="4" maxlength="800" placeholder="Tell us about your business or preferred quantity" data-placeholder-en="Tell us about your business or preferred quantity" data-placeholder-bn="আপনার ব্যবসা বা সম্ভাব্য অর্ডার সম্পর্কে লিখুন"></textarea></label>
            <button class="btn btn-green" type="submit" data-en="Submit Enquiry" data-bn="ইনকোয়ারি সাবমিট করুন">Submit Enquiry</button>
            <p class="form-status" id="formStatus" role="status"></p>
            <div class="form-success-actions-v39" id="formSuccessActions" hidden>
                <a class="btn btn-red" id="formWhatsAppLink" href="<?= e(whatsapp_url($settings['whatsapp_number'] ?? '', 'Hello, I would like distributor information.')) ?>" target="_blank" rel="noopener" data-en="Continue on WhatsApp" data-bn="WhatsApp-এ কথা বলুন">Continue on WhatsApp</a>
                <small data-en="Your lead is saved. WhatsApp is optional for faster response." data-bn="আপনার ইনকোয়ারি জমা হয়েছে। দ্রুত রেসপন্সের জন্য WhatsApp করতে পারেন।">Your lead is saved. WhatsApp is optional for faster response.</small>
            </div>
        </form>
    </div>
</section>



<section id="office-location" class="section-pad office-location-section office-location-v18">
    <div class="container office-location-shell">
        <div class="office-location-copy reveal">
            <span class="eyebrow" data-en="Office Location" data-bn="অফিস লোকেশন">Office Location</span>
            <h2 data-en="Visit Us Today" data-bn="চলে আসুন আজই">Visit Us Today</h2>
            <p data-en="Find our office in Lalmatia, Dhaka, view the map and open turn-by-turn directions directly in Google Maps." data-bn="লালমাটিয়া, ঢাকায় আমাদের অফিস লোকেশন দেখুন এবং Google Maps-এ সরাসরি দিকনির্দেশনা খুলুন।">Find our office in Lalmatia, Dhaka, view the map and open turn-by-turn directions directly in Google Maps.</p>
            <div class="office-info-card">
                <div><small data-en="Office" data-bn="অফিস">Office</small><strong>Nabiad Distribution Limited</strong></div>
                <div><small data-en="Area" data-bn="এলাকা">Area</small><span data-en="Lalmatia, Dhaka, Bangladesh" data-bn="লালমাটিয়া, ঢাকা, বাংলাদেশ">Lalmatia, Dhaka, Bangladesh</span></div>
                <div><small data-en="Coordinates" data-bn="কোঅর্ডিনেট">Coordinates</small><span>23.756893, 90.367703</span></div>
            </div>
            <div class="office-location-actions">
                <a class="btn btn-green" href="https://www.google.com/maps/dir/?api=1&destination=23.756893,90.367703" target="_blank" rel="noopener" data-en="Open Directions" data-bn="দিকনির্দেশনা খুলুন">Open Directions</a>
                <a class="btn btn-ghost" href="https://www.google.com/maps/search/?api=1&query=23.756893,90.367703" target="_blank" rel="noopener" data-en="View on Google Maps" data-bn="Google Maps-এ দেখুন">View on Google Maps</a>
            </div>
        </div>
        <div class="office-map-wrap reveal">
            <div class="office-map-top"><span data-en="Live Map Preview" data-bn="লাইভ ম্যাপ প্রিভিউ">Live Map Preview</span><b>Nabiad Distribution Ltd.</b></div>
            <div class="office-map-card office-map-card-v18">
                <div class="map-lazy-cover-v39" data-map-lazy><button type="button" class="btn btn-green" data-en="Load Map Preview" data-bn="ম্যাপ প্রিভিউ দেখুন">Load Map Preview</button><small data-en="Map loads only when needed for faster page speed." data-bn="পেজ দ্রুত রাখতে প্রয়োজন হলে ম্যাপ লোড হবে।">Map loads only when needed for faster page speed.</small></div><iframe title="Nabiad Distribution Limited office location map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" data-src="https://www.google.com/maps?q=23.756893,90.367703&z=16&output=embed"></iframe>
            </div>
        </div>
    </div>
</section>

<section id="contact" class="section-pad contact-section">
    <div class="container contact-grid contact-only-grid">
        <div class="section-head reveal">
            <span class="eyebrow" data-en="Contact Us" data-bn="যোগাযোগ করুন">Contact Us</span>
            <h2 data-en="Sales, Product & Distribution Support" data-bn="সেলস, পণ্য ও ডিস্ট্রিবিউশন সাপোর্ট">Sales, Product & Distribution Support</h2>
            <p data-en="For product enquiries, catalogue requests, dealer support or distribution opportunities, contact our team directly." data-bn="পণ্যের ইনকোয়ারি, ক্যাটালগ, ডিলার সাপোর্ট বা ডিস্ট্রিবিউশন সুযোগের জন্য সরাসরি আমাদের টিমের সাথে যোগাযোগ করুন।">For product enquiries, catalogue requests, dealer support or distribution opportunities, contact our team directly.</p>
        </div>
        <div class="contact-cards reveal">
            <a href="tel:<?= e($settings['contact_phone']) ?>">☎ <span><?= e($settings['contact_phone']) ?></span></a>
            <a href="https://wa.me/<?= e($settings['whatsapp_number']) ?>" target="_blank" rel="noopener">WhatsApp <span>+<?= e($settings['whatsapp_number']) ?></span></a>
            <a href="mailto:<?= e($settings['email']) ?>">✉ <span><?= e($settings['email']) ?></span></a>
            <a href="<?= e($settings['facebook']) ?>" target="_blank" rel="noopener">Facebook <span>Visit Page</span></a>
            <a href="<?= e($catalogueUrl) ?>" data-catalogue-download>⬇ <span data-en="Download Catalogue" data-bn="ক্যাটালগ ডাউনলোড">Download Catalogue</span></a>
            <address data-en="<?= e($settings['office_address_en']) ?>" data-bn="<?= e($settings['office_address_bn']) ?>"><?= e($settings['office_address_en']) ?></address>
        </div>
    </div>
</section>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div><div class="brand-lockup footer-brand"><span class="brand-logo brand-logo-atoz"><img src="<?= e(asset_url('assets/img/logo-atoz.webp')) ?>" alt="A TO Z" width="1598" height="1041"></span><span class="brand-logo brand-logo-signal"><img src="<?= e(asset_url('assets/img/logo-signal.webp')) ?>" alt="SIGNAL" width="2047" height="880"></span></div><p data-en="A TO Z and SIGNAL provide dependable mosquito protection solutions trusted by households, retailers and distribution partners across Bangladesh." data-bn="A TO Z এবং SIGNAL বাংলাদেশজুড়ে পরিবার, রিটেইলার ও ডিস্ট্রিবিউশন পার্টনারদের জন্য নির্ভরযোগ্য মশা সুরক্ষা সমাধান প্রদান করে।">A TO Z and SIGNAL provide dependable mosquito protection solutions trusted by households, retailers and distribution partners across Bangladesh.</p></div>
        <div><h4>Quick Links</h4><a href="<?= e(home_url('home')) ?>">Home</a><a href="<?= e(home_url('products')) ?>">Products</a><a href="<?= e(page_url('about')) ?>">About Us</a><a href="<?= e(page_url('blogs')) ?>">Blog / Archive</a><a href="<?= e(home_url('network')) ?>">Distributors</a><a href="<?= e(home_url('office-location')) ?>">Office Location</a><a href="<?= e(home_url('contact')) ?>">Contact Us</a><a href="<?= e(page_url('privacy')) ?>">Privacy Policy</a><a href="<?= e(page_url('terms')) ?>">Terms & Conditions</a></div>
        <div><h4>Our Products</h4><?php foreach(array_slice($products,0,7) as $p): ?><a href="<?= e(product_url($p)) ?>"><?= e($p['name_en']) ?></a><?php endforeach; ?></div>
        <div><h4>Contact Info</h4><a href="tel:<?= e($settings['contact_phone']) ?>">Phone<br><?= e($settings['contact_phone']) ?></a><a href="https://wa.me/<?= e($settings['whatsapp_number']) ?>" target="_blank" rel="noopener">WhatsApp<br>+<?= e($settings['whatsapp_number']) ?></a><a href="mailto:<?= e($settings['email']) ?>">Email<br><?= e($settings['email']) ?></a><a href="<?= e($settings['facebook']) ?>" target="_blank" rel="noopener">Facebook<br>NabiadAtoZ</a><p><?= e($settings['office_address_en']) ?></p></div>
    </div>
    <div class="copyright">© <?= date('Y') ?> <?= e($settings['site_name']) ?>. All Rights Reserved. <span><a href="<?= e(page_url('privacy')) ?>">Privacy Policy</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?= e(page_url('terms')) ?>">Terms & Conditions</a></span></div>
</footer>

<div class="product-modal" id="productModal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modalProductName">
    <div class="product-modal-backdrop" data-modal-close></div>
    <div class="product-modal-dialog" role="document">
        <button class="product-modal-close" id="productModalClose" type="button" data-modal-close aria-label="Close product details">×</button>
        <div class="product-modal-visual">
            <span class="modal-visual-glow"></span>
            <img id="modalProductImage" src="" alt="" loading="lazy">
            <span class="modal-coil-line" aria-hidden="true"></span>
            <span class="modal-ember" aria-hidden="true"></span>
        </div>
        <div class="product-modal-copy">
            <span class="eyebrow modal-eyebrow" id="modalProductBadge">Premium Product</span>
            <h2 id="modalProductName">Product Name</h2>
            <p id="modalProductDesc">Product description</p>
            <div class="modal-protection" id="modalProductHours">12H Protection</div>
            <div class="modal-features" id="modalProductFeatures"></div>
            <div class="modal-actions">
                <a class="btn btn-red" id="modalProductPage" href="<?= e(home_url('products')) ?>">Open Full Page →</a>
                <a class="btn btn-green" id="modalProductEnquiry" href="#distributorForm" data-en="Enquire Product →" data-bn="পণ্য ইনকোয়ারি →">Enquire Product →</a>
                <a class="btn btn-ghost" id="modalProductCatalogue" href="<?= e($catalogueUrl) ?>">Catalogue ↓</a>
            </div>
        </div>
    </div>
</div>

<script>
window.APP = { csrf: "<?= e(csrf_token()) ?>", products: <?= json_encode($productJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?> };
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer onerror="this.onerror=null;this.src='<?= e(asset_url('assets/vendor/gsap-fallback.js')) ?>?v=<?= e(app_asset_version()) ?>';"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js" defer onerror="this.onerror=null;this.src='<?= e(asset_url('assets/vendor/three-fallback.js')) ?>?v=<?= e(app_asset_version()) ?>';"></script>
<script>window.ATOZ_ROUTES={productBase:<?= json_encode(rtrim(base_url('product'), '/') . '/') ?>,blogBase:<?= json_encode(rtrim(base_url('blog'), '/') . '/') ?>,catalogue:<?= json_encode(rtrim(base_url('catalogue'), '/')) ?>,home:<?= json_encode(base_url()) ?>};</script>
<script src="<?= e(asset_url('assets/js/main.js')) ?>?v=<?= e(app_asset_version()) ?>" defer></script>
</body>
</html>

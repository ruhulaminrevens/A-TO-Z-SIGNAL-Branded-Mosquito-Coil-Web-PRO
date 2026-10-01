<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/site-ui.php';
ensure_phase2_cms_schema();
$settings = get_site_settings();
$products = get_products(true);
$leaders = get_team_members(true);
$timeline = get_timeline_entries(true);
$actionEn = split_lines(cms_setting($settings, 'action_plan_en'));
$actionBn = split_lines(cms_setting($settings, 'action_plan_bn'));
public_header($settings, 'About Us', 'Learn about A TO Z and SIGNAL mosquito coil brands, leadership, mission, vision and company archive.', 'about');
?>
<main id="main-content" class="inner-page-main about-page-v28 about-page-v31 about-page-v32 about-page-v34 about-page-v35 about-page-v36 about-page-v37">
    <section class="about-hero-v28 section-pad">
        <div class="container about-hero-grid-v28">
            <div class="about-copy-v28 reveal">
                <span class="eyebrow" data-en="<?= e(cms_setting($settings, 'about_eyebrow_en')) ?>" data-bn="<?= e(cms_setting($settings, 'about_eyebrow_bn')) ?>"><?= e(cms_setting($settings, 'about_eyebrow_en')) ?></span>
                <h1 data-en="<?= e(cms_setting($settings, 'about_title_en')) ?>" data-bn="<?= e(cms_setting($settings, 'about_title_bn')) ?>"><?= e(cms_setting($settings, 'about_title_en')) ?></h1>
                <p data-en="<?= e(cms_setting($settings, 'about_intro_en')) ?>" data-bn="<?= e(cms_setting($settings, 'about_intro_bn')) ?>"><?= e(cms_setting($settings, 'about_intro_en')) ?></p>
                <div class="about-trust-row-v29 about-trust-row-v31">
                    <span><b data-en="Trade Discipline" data-bn="ট্রেড শৃঙ্খলা">Trade Discipline</b><em data-en="Structured distribution" data-bn="সুশৃঙ্খল ডিস্ট্রিবিউশন">Structured distribution</em></span>
                    <span><b data-en="Product Availability" data-bn="পণ্য প্রাপ্যতা">Product Availability</b><em data-en="Regular market supply" data-bn="নিয়মিত বাজার সরবরাহ">Regular market supply</em></span>
                    <span><b data-en="Retail Support" data-bn="রিটেইল সাপোর্ট">Retail Support</b><em data-en="Dealer and distributor focus" data-bn="ডিলার ও ডিস্ট্রিবিউটর ফোকাস">Dealer and distributor focus</em></span>
                </div>
            </div>
            <div class="about-visual-v28 about-visual-v36 reveal" aria-label="Brand trust overview">
                <div class="about-orbit-v28"><span>A TO Z</span><i></i><span>SIGNAL</span></div>
                <div class="about-visual-core-v36" aria-hidden="true">
                    <span class="core-logo-v36"><img src="<?= e(asset_url('assets/img/logo-atoz.webp')) ?>" alt=""></span>
                    <i></i>
                    <span class="core-logo-v36"><img src="<?= e(asset_url('assets/img/logo-signal.webp')) ?>" alt=""></span>
                </div>
                <div class="about-stat-v28 red"><b>2</b><span data-en="Focused mosquito coil brands" data-bn="ফোকাসড মশার কয়েল ব্র্যান্ড">Focused mosquito coil brands</span></div>
                <div class="about-stat-v28 green"><b>64</b><span data-en="64 District Coverage" data-bn="৬৪ জেলা কভারেজ">64 District Coverage</span></div>
                <div class="about-stat-v28 gold"><b>24/7</b><span data-en="Household protection mindset" data-bn="পরিবার সুরক্ষার মানসিকতা">Household protection mindset</span></div>
            </div>
        </div>
    </section>

    <section class="section-pad-sm about-brand-section-v28">
        <div class="container">
            <div class="section-head compact-head reveal">
                <span class="eyebrow" data-en="Brand Ownership" data-bn="ব্র্যান্ড পরিচিতি">Brand Ownership</span>
                <h2 data-en="Clear Product Identity for Trade Partners" data-bn="ট্রেড পার্টনারদের জন্য পরিষ্কার পণ্য পরিচিতি">Clear Product Identity for Trade Partners</h2>
                <p data-en="The brand story is presented clearly so retailers, distributors and customers understand the role of each brand." data-bn="রিটেইলার, ডিস্ট্রিবিউটর এবং কাস্টমার যেন প্রতিটি ব্র্যান্ডের পরিচয় সহজে বুঝতে পারে—সেভাবেই ব্র্যান্ড স্টোরি সাজানো হয়েছে।">The brand story is presented clearly so retailers, distributors and customers understand the role of each brand.</p>
            </div>
            <div class="about-brand-grid-v28">
                <article class="about-brand-card-v28 atoz reveal">
                    <img src="<?= e(asset_url('assets/img/logo-atoz.webp')) ?>" alt="A TO Z" loading="lazy">
                    <h3>A TO Z</h3>
                    <p data-en="A TO Z branded mosquito coils are manufactured by Nabiad Distribution Limited and positioned for dependable daily household protection." data-bn="A TO Z ব্র্যান্ডের মশার কয়েল নাবীয়াদ ডিস্ট্রিবিউশন লিমিটেড কর্তৃক প্রস্তুত এবং নির্ভরযোগ্য দৈনন্দিন পারিবারিক সুরক্ষার জন্য পজিশনড।">A TO Z branded mosquito coils are manufactured by Nabiad Distribution Limited and positioned for dependable daily household protection.</p>
                </article>
                <article class="about-brand-card-v28 signal reveal">
                    <img src="<?= e(asset_url('assets/img/logo-signal.webp')) ?>" alt="SIGNAL" loading="lazy">
                    <h3>SIGNAL</h3>
                    <p data-en="SIGNAL branded mosquito coils are manufactured by NZ Corporation with a modern low-smoke, comfort-focused product story." data-bn="SIGNAL ব্র্যান্ডের মশার কয়েল এন জেড কর্পোরেশন কর্তৃক প্রস্তুত, যেখানে আধুনিক কম ধোঁয়া ও আরামদায়ক ব্যবহারের পণ্য গল্প রয়েছে।">SIGNAL branded mosquito coils are manufactured by NZ Corporation with a modern low-smoke, comfort-focused product story.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section-pad-sm leadership-section-v28 leadership-section-v31">
        <div class="container">
            <div class="section-head compact-head reveal">
                <span class="eyebrow" data-en="Leadership Team" data-bn="লিডারশিপ টিম">Leadership Team</span>
                <h2 data-en="Experienced People Behind the Network" data-bn="নেটওয়ার্কের পেছনের অভিজ্ঞ নেতৃত্ব">Experienced People Behind the Network</h2>
                <p data-en="Strategy, administration, operations and national sales execution are aligned to support sustainable brand growth." data-bn="টেকসই ব্র্যান্ড বৃদ্ধির জন্য কৌশল, প্রশাসন, অপারেশন এবং জাতীয় সেলস এক্সিকিউশনকে একসাথে সমন্বয় করা হয়।">Strategy, administration, operations and national sales execution are aligned to support sustainable brand growth.</p>
            </div>
            <div class="leader-grid-v28 leader-grid-v31">
                <?php foreach($leaders as $leader): ?>
                    <article class="leader-card-v28 leader-card-v31 reveal">
                        <div class="leader-photo-v28"><img src="<?= e(optimized_image_url($leader['image'] ?? '')) ?>" alt="<?= e($leader['name_en'] ?? '') ?> - <?= e($leader['role_en'] ?? '') ?>" loading="lazy"></div>
                        <div class="leader-body-v28">
                            <h3 data-en="<?= e($leader['name_en'] ?? '') ?>" data-bn="<?= e($leader['name_bn'] ?? '') ?>"><?= e($leader['name_en'] ?? '') ?></h3>
                            <strong data-en="<?= e($leader['role_en'] ?? '') ?>" data-bn="<?= e($leader['role_bn'] ?? '') ?>"><?= e($leader['role_en'] ?? '') ?></strong>
                            <p data-en="<?= e($leader['intro_en'] ?? '') ?>" data-bn="<?= e($leader['intro_bn'] ?? '') ?>"><?= e($leader['intro_en'] ?? '') ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section-pad-sm mission-section-v28 mission-section-v31">
        <div class="container mission-grid-v28">
            <article class="mission-card-v28 reveal"><span>01</span><h2 data-en="Our Mission" data-bn="আমাদের মিশন">Our Mission</h2><p data-en="<?= e(cms_setting($settings,'mission_text_en')) ?>" data-bn="<?= e(cms_setting($settings,'mission_text_bn')) ?>"><?= e(cms_setting($settings,'mission_text_en')) ?></p></article>
            <article class="mission-card-v28 reveal"><span>02</span><h2 data-en="Our Vision" data-bn="আমাদের ভিশন">Our Vision</h2><p data-en="<?= e(cms_setting($settings,'vision_text_en')) ?>" data-bn="<?= e(cms_setting($settings,'vision_text_bn')) ?>"><?= e(cms_setting($settings,'vision_text_en')) ?></p></article>
            <article class="mission-card-v28 reveal"><span>03</span><h2 data-en="Action Plan" data-bn="অ্যাকশন প্ল্যান">Action Plan</h2><ul><?php foreach($actionEn as $i => $line): $bn = $actionBn[$i] ?? $line; ?><li data-en="<?= e($line) ?>" data-bn="<?= e($bn) ?>"><?= e($line) ?></li><?php endforeach; ?></ul></article>
        </div>
    </section>

    <section class="section-pad-sm about-archive-section-v28 about-archive-section-v31">
        <div class="container">
            <div class="section-head compact-head reveal"><span class="eyebrow" data-en="Company Timeline" data-bn="কোম্পানি টাইমলাইন">Company Timeline</span><h2 data-en="Sales Meets, Product Launches & Team Moments" data-bn="সেলস মিট, পণ্য লঞ্চ ও টিম মুহূর্ত">Sales Meets, Product Launches & Team Moments</h2><p data-en="Explore the moments that shaped our team, product launches and distribution journey." data-bn="আমাদের টিম, পণ্য লঞ্চ এবং ডিস্ট্রিবিউশন যাত্রার গুরুত্বপূর্ণ মুহূর্তগুলো দেখুন।">Explore the moments that shaped our team, product launches and distribution journey.</p></div>
            <div class="archive-gallery-v28 archive-lightbox-grid-v30 timeline-gallery-v31">
                <?php foreach($timeline as $entry): ?>
                    <a href="<?= e($entry['image'] ?? '') ?>" data-lightbox-gallery="company-archive" data-lightbox-title="<?= e($entry['title_en'] ?? '') ?>">
                        <img src="<?= e(optimized_image_url($entry['image'] ?? '')) ?>" alt="<?= e($entry['title_en'] ?? '') ?>" loading="lazy">
                        <span><?= e($entry['year_label'] ?? '') ?></span>
                        <strong data-en="<?= e($entry['title_en'] ?? '') ?>" data-bn="<?= e($entry['title_bn'] ?? '') ?>"><?= e($entry['title_en'] ?? '') ?></strong>
                        <em data-en="View Photo" data-bn="ছবি দেখুন">View Photo</em>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="gallery-actions-v30"><a class="btn btn-ghost" href="<?= e(page_url('blogs')) ?>" data-en="View Full Archive" data-bn="সম্পূর্ণ আর্কাইভ দেখুন">View Full Archive</a></div>
        </div>
    </section>
</main>
<?php public_footer($settings, $products); ?>

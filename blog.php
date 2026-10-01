<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/site-ui.php';

$settings = get_site_settings();
$products = get_products(true);
$filters = [
    'q' => trim((string)($_GET['q'] ?? '')),
    'category' => trim((string)($_GET['category'] ?? '')),
    'tag' => trim((string)($_GET['tag'] ?? '')),
    'year' => trim((string)($_GET['year'] ?? '')),
];
$page = max(1, (int)($_GET['page'] ?? 1));
$result = get_blog_posts_filtered($filters, $page, 6);
$posts = $result['posts'];
$categories = blog_categories();
$years = blog_years();
$activeFilters = array_filter($filters, static fn($v) => trim((string)$v) !== '');
$featured = get_featured_blog_post();
if ($featured && empty($activeFilters) && $page === 1) {
    $posts = array_values(array_filter($posts, static fn($post) => (string)($post['slug'] ?? '') !== (string)($featured['slug'] ?? '')));
}

public_header($settings, 'Blogs', 'Company blog and event archive for Nabiad Distribution Limited, A TO Z and SIGNAL mosquito coil brand activities.', 'blogs');
?>
<main id="main-content" class="inner-page-main blog-page-v28 blog-page-v33 blog-page-v34">
    <section class="blog-hero-v28 blog-hero-v33 section-pad">
        <div class="container blog-hero-grid-v28 blog-hero-grid-v33">
            <div class="reveal">
                <span class="eyebrow trusted" data-en="Company Blogs & Archive" data-bn="কোম্পানি ব্লগ ও আর্কাইভ">Company Blogs & Archive</span>
                <h1 data-en="Brand Updates, Sales Meets & Distribution Stories" data-bn="ব্র্যান্ড আপডেট, সেলস মিট ও ডিস্ট্রিবিউশন গল্প">Brand Updates, Sales Meets & Distribution Stories</h1>
                <p data-en="Explore product launches, team events, sales meets and market communication updates from Nabiad Distribution Limited." data-bn="নাবীয়াদ ডিস্ট্রিবিউশন লিমিটেডের পণ্য লঞ্চ, টিম ইভেন্ট, সেলস মিট এবং বাজার যোগাযোগের আপডেট দেখুন।">Explore product launches, team events, sales meets and market communication updates from Nabiad Distribution Limited.</p>
            </div>
            <div class="blog-hero-count-v28 blog-hero-count-v33 reveal"><b><?= (int)$result['total'] ?></b><span data-en="Matching stories" data-bn="ম্যাচিং গল্প">Matching stories</span></div>
        </div>
    </section>

    <section class="section-pad-sm blog-filter-section-v33">
        <div class="container">
            <form class="blog-filter-card-v33 reveal" method="get" action="<?= e(page_url('blogs')) ?>">
                <label><span data-en="Search" data-bn="সার্চ">Search</span><input name="q" value="<?= e($filters['q']) ?>" placeholder="Search stories..." data-placeholder-en="Search stories..." data-placeholder-bn="স্টোরি খুঁজুন..."></label>
                <label><span data-en="Category" data-bn="ক্যাটাগরি">Category</span><select name="category"><option value="">All categories</option><?php foreach($categories as $cat): ?><option value="<?= e($cat) ?>" <?= $filters['category']===$cat?'selected':'' ?>><?= e($cat) ?></option><?php endforeach; ?></select></label>
                <label><span data-en="Year" data-bn="বছর">Year</span><select name="year"><option value="">All years</option><?php foreach($years as $yr): ?><option value="<?= e($yr) ?>" <?= $filters['year']===$yr?'selected':'' ?>><?= e($yr) ?></option><?php endforeach; ?></select></label>
                <div class="blog-filter-actions-v33"><button class="btn btn-red" type="submit" data-en="Filter Stories" data-bn="স্টোরি ফিল্টার করুন">Filter Stories</button><a class="btn btn-ghost" href="<?= e(page_url('blogs')) ?>" data-en="Reset" data-bn="রিসেট">Reset</a></div>
            </form>
            <?php if($activeFilters): ?><div class="active-blog-filters-v33"><?php foreach($activeFilters as $key=>$value): ?><span><?= e(ucwords(str_replace('_',' ', $key))) ?>: <?= e($value) ?></span><?php endforeach; ?></div><?php endif; ?>
        </div>
    </section>

    <?php if($featured && empty($activeFilters)): ?>
    <section class="section-pad-sm featured-blog-section-v28 featured-blog-section-v33">
        <div class="container">
            <article class="featured-blog-v28 featured-blog-v33 reveal">
                <a class="featured-blog-img-v28" href="<?= e(blog_post_url($featured)) ?>"><img src="<?= e(optimized_image_url($featured['image'] ?: 'assets/img/hero-atoz-pack.png')) ?>" alt="<?= e($featured['title_en']) ?>" loading="lazy"></a>
                <div class="featured-blog-body-v28">
                    <span class="eyebrow" data-en="Featured Story" data-bn="ফিচার্ড স্টোরি">Featured Story</span>
                    <div class="blog-chip-row-v33"><span><?= e($featured['category'] ?? blog_default_category()) ?></span><?php foreach(array_slice(parse_blog_tags($featured['tags'] ?? ''),0,3) as $tag): ?><a href="<?= e(blog_archive_url(['tag' => $tag])) ?>">#<?= e($tag) ?></a><?php endforeach; ?></div>
                    <time datetime="<?= e((string)($featured['published_at'] ?? '')) ?>"><?= e(format_blog_date($featured['published_at'] ?? '')) ?></time>
                    <h2><a href="<?= e(blog_post_url($featured)) ?>" data-en="<?= e($featured['title_en']) ?>" data-bn="<?= e($featured['title_bn'] ?: $featured['title_en']) ?>"><?= e($featured['title_en']) ?></a></h2>
                    <p data-en="<?= e($featured['excerpt_en'] ?? '') ?>" data-bn="<?= e($featured['excerpt_bn'] ?: ($featured['excerpt_en'] ?? '')) ?>"><?= e($featured['excerpt_en'] ?? '') ?></p>
                    <a class="btn btn-red" href="<?= e(blog_post_url($featured)) ?>" data-en="Read Full Story" data-bn="পুরো গল্প পড়ুন">Read Full Story</a>
                </div>
            </article>
        </div>
    </section>
    <?php endif; ?>

    <section class="section-pad-sm blog-list-section-v28 blog-list-section-v33">
        <div class="container">
            <div class="section-head compact-head reveal"><span class="eyebrow" data-en="Latest Posts" data-bn="সর্বশেষ পোস্ট">Latest Posts</span><h2 data-en="Company Archive" data-bn="কোম্পানি আর্কাইভ">Company Archive</h2><p data-en="Browse all published event and brand update posts." data-bn="প্রকাশিত সব ইভেন্ট ও ব্র্যান্ড আপডেট পোস্ট দেখুন।">Browse all published event and brand update posts.</p></div>
            <div class="blog-grid-v28 blog-grid-v33">
                <?php foreach($posts as $post): ?>
                    <article class="blog-card-v28 blog-card-v33 reveal">
                        <a class="blog-card-img-v28" href="<?= e(blog_post_url($post)) ?>"><img src="<?= e(optimized_image_url($post['image'] ?: 'assets/img/hero-atoz-pack.png')) ?>" alt="<?= e($post['title_en']) ?>" loading="lazy"></a>
                        <div class="blog-card-body-v28">
                            <div class="blog-chip-row-v33"><span><?= e($post['category'] ?? blog_default_category()) ?></span><?php if(!empty($post['is_featured'])): ?><b>Featured</b><?php endif; ?></div>
                            <time datetime="<?= e((string)($post['published_at'] ?? '')) ?>"><?= e(format_blog_date($post['published_at'] ?? '')) ?></time>
                            <h2><a href="<?= e(blog_post_url($post)) ?>" data-en="<?= e($post['title_en']) ?>" data-bn="<?= e($post['title_bn'] ?: $post['title_en']) ?>"><?= e($post['title_en']) ?></a></h2>
                            <p data-en="<?= e($post['excerpt_en'] ?? '') ?>" data-bn="<?= e($post['excerpt_bn'] ?: ($post['excerpt_en'] ?? '')) ?>"><?= e($post['excerpt_en'] ?? '') ?></p>
                            <div class="blog-tag-list-v33"><?php foreach(array_slice(parse_blog_tags($post['tags'] ?? ''),0,3) as $tag): ?><a href="<?= e(blog_archive_url(['tag' => $tag])) ?>">#<?= e($tag) ?></a><?php endforeach; ?></div>
                            <a class="blog-read" href="<?= e(blog_post_url($post)) ?>" data-en="Read More →" data-bn="আরও পড়ুন →">Read More →</a>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if(!$posts): ?><div class="empty-state-v28 empty-state-card-v34"><b data-en="No stories found" data-bn="কোনো স্টোরি পাওয়া যায়নি">No stories found</b><p data-en="Try another keyword, category or year filter." data-bn="অন্য কীওয়ার্ড, ক্যাটাগরি বা বছর দিয়ে আবার চেষ্টা করুন।">Try another keyword, category or year filter.</p><a class="btn btn-ghost" href="<?= e(page_url('blogs')) ?>" data-en="Clear Filters" data-bn="ফিল্টার মুছুন">Clear Filters</a></div><?php endif; ?>
            </div>
            <?php if($result['pages'] > 1): ?>
                <nav class="blog-pagination-v33" aria-label="Blog pagination">
                    <?php for($i=1;$i<=$result['pages'];$i++): $params=array_merge($_GET,['page'=>$i]); ?>
                        <a class="<?= $i===$result['page']?'active':'' ?>" href="<?= e(blog_archive_url($params)) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php public_footer($settings, $products); ?>

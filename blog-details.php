<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/site-ui.php';

$settings = get_site_settings();
$products = get_products(true);
$slug = trim((string)($_GET['slug'] ?? ''));
if ($slug === '') {
    $path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if (preg_match('~^blog/([^/]+)$~', $path, $m)) $slug = rawurldecode($m[1]);
}
ensure_phase43_media_content_schema();
$post = get_blog_post_by_slug($slug);
if (!$post) { require __DIR__ . '/404.php'; exit; }
$related = get_related_blog_posts($post, 3);
$shareUrl = blog_share_url($post);
$seoTitle = trim((string)($post['seo_title'] ?? '')) ?: $post['title_en'];
$seoDesc = trim((string)($post['seo_description'] ?? '')) ?: ($post['excerpt_en'] ?? 'Company blog and event archive for A TO Z and SIGNAL.');

public_header($settings, $seoTitle, $seoDesc, 'blogs');
?>
<?= json_ld(breadcrumb_schema([['name'=>'Home','url'=>home_url()],['name'=>'Blogs','url'=>page_url('blogs')],['name'=>$post['title_en'] ?? 'Story','url'=>blog_post_url($post)]])) ?>
<?= json_ld(article_schema($post, $settings)) ?>
<main id="main-content" class="inner-page-main blog-detail-page-v28 blog-detail-page-v33 blog-detail-page-v34">
    <article class="blog-detail-v28 blog-detail-v33 section-pad">
        <div class="container blog-detail-container-v28">
            <a class="blog-read back-link-v28" href="<?= e(page_url('blogs')) ?>" data-en="← Back to Blogs" data-bn="← ব্লগে ফিরে যান">← Back to Blogs</a>
            <header class="blog-detail-head-v28 blog-detail-head-v33 reveal">
                <span class="eyebrow trusted" data-en="Company Story" data-bn="কোম্পানি স্টোরি">Company Story</span>
                <div class="blog-chip-row-v33 detail"><span><?= e($post['category'] ?? blog_default_category()) ?></span><?php foreach(array_slice(parse_blog_tags($post['tags'] ?? ''),0,4) as $tag): ?><a href="<?= e(blog_archive_url(['tag' => $tag])) ?>">#<?= e($tag) ?></a><?php endforeach; ?></div>
                <h1 data-en="<?= e($post['title_en']) ?>" data-bn="<?= e($post['title_bn'] ?: $post['title_en']) ?>"><?= e($post['title_en']) ?></h1>
                <div class="blog-meta-v28"><time datetime="<?= e((string)($post['published_at'] ?? '')) ?>"><?= e(format_blog_date($post['published_at'] ?? '')) ?></time><span>A TO Z & SIGNAL</span></div>
                <p data-en="<?= e($post['excerpt_en'] ?? '') ?>" data-bn="<?= e($post['excerpt_bn'] ?: ($post['excerpt_en'] ?? '')) ?>"><?= e($post['excerpt_en'] ?? '') ?></p>
                <div class="blog-share-v33" aria-label="Share this story">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= e(rawurlencode($shareUrl)) ?>" target="_blank" rel="noopener">Facebook</a>
                    <a href="https://wa.me/?text=<?= e(rawurlencode(($post['title_en'] ?? 'A TO Z & SIGNAL story') . ' ' . $shareUrl)) ?>" target="_blank" rel="noopener">WhatsApp</a>
                    <button type="button" data-copy-link="<?= e($shareUrl) ?>">Copy Link</button>
                </div>
            </header>
            <?php if(!empty($post['image'])): ?><img class="blog-detail-img-v28 reveal" src="<?= e(optimized_image_url($post['image'])) ?>" alt="<?= e($post['title_en']) ?>" loading="eager" fetchpriority="high" decoding="async"<?= image_dimension_attributes($post['image']) ?>><?php endif; ?>
            <div class="blog-content-v28 blog-content-v33 reveal" data-en="<?= e($post['content_en'] ?: ($post['excerpt_en'] ?? '')) ?>" data-bn="<?= e($post['content_bn'] ?: ($post['excerpt_bn'] ?: ($post['content_en'] ?? ''))) ?>">
                <?= blog_content_html($post['content_en'] ?: ($post['excerpt_en'] ?? '')) ?>
            </div>
            <?php $attachedBlocks = normalize_json_list($post['content_blocks_json'] ?? ''); ?>
            <?php if($attachedBlocks): ?><div class="blog-attached-blocks-v43 reveal"><?php foreach($attachedBlocks as $blockKey): ?><?= render_content_block_by_key($blockKey) ?><?php endforeach; ?></div><?php endif; ?>
        </div>
    </article>

    <?php if($related): ?>
    <section class="section-pad-sm related-blog-section-v28 related-blog-section-v33">
        <div class="container">
            <div class="section-head compact-head"><span class="eyebrow" data-en="More Stories" data-bn="আরও গল্প">More Stories</span><h2 data-en="Related Updates" data-bn="সম্পর্কিত আপডেট">Related Updates</h2></div>
            <div class="blog-grid-v28 blog-grid-v33 related-grid-v28">
                <?php foreach($related as $item): ?>
                    <article class="blog-card-v28 blog-card-v33">
                        <a class="blog-card-img-v28" href="<?= e(blog_post_url($item)) ?>"><img src="<?= e(optimized_image_url($item['image'] ?: 'assets/img/hero-atoz-pack.png')) ?>" alt="<?= e($item['title_en']) ?>" loading="lazy"></a>
                        <div class="blog-card-body-v28"><div class="blog-chip-row-v33"><span><?= e($item['category'] ?? blog_default_category()) ?></span></div><time datetime="<?= e((string)($item['published_at'] ?? '')) ?>"><?= e(format_blog_date($item['published_at'] ?? '')) ?></time><h2><a href="<?= e(blog_post_url($item)) ?>" data-en="<?= e($item['title_en']) ?>" data-bn="<?= e($item['title_bn'] ?: $item['title_en']) ?>"><?= e($item['title_en']) ?></a></h2><a class="blog-read" href="<?= e(blog_post_url($item)) ?>" data-en="Read More →" data-bn="আরও পড়ুন →">Read More →</a></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>
<?php public_footer($settings, $products); ?>

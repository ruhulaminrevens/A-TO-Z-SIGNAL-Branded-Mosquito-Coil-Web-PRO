<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase5_marketing_schema();
$error = '';
$flash = '';
$seoKeys = [
    'global_seo_title_suffix','global_seo_keywords','global_og_image',
    'organization_name','organization_legal_name','organization_logo','organization_description',
    'organization_phone','organization_email','organization_address',
    'marketing_cta_en','marketing_cta_bn','conversion_event_label',
    'google_analytics_id','meta_pixel_id','google_site_verification'
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $og = upload_image_file('global_og_file', 'uploads/seo', 'global-og-image', 3 * 1024 * 1024);
        if ($og) $_POST['global_og_image'] = $og;
        $logo = upload_image_file('organization_logo_file', 'uploads/seo', 'organization-logo', 2 * 1024 * 1024);
        if ($logo) $_POST['organization_logo'] = $logo;
        save_site_settings($seoKeys, $_POST);
        log_admin_action('seo_marketing_save', 'SEO and marketing settings were updated.');
        $flash = 'SEO and marketing settings saved.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
$settings = get_site_settings();
$checks = [
    'SEO keywords' => trim(cms_setting($settings, 'global_seo_keywords')) !== '',
    'Open Graph image' => trim(cms_setting($settings, 'global_og_image')) !== '',
    'Organization name' => trim(cms_setting($settings, 'organization_name')) !== '',
    'Organization logo' => trim(cms_setting($settings, 'organization_logo')) !== '',
    'Analytics / Pixel' => trim(cms_setting($settings, 'google_analytics_id')) !== '' || trim(cms_setting($settings, 'meta_pixel_id')) !== '',
    'Search Console verification' => trim(cms_setting($settings, 'google_site_verification')) !== '',
];
$score = (int)round((count(array_filter($checks)) / max(1, count($checks))) * 100);
admin_header('SEO & Marketing');
?>
<?php if($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<section class="panel admin-list-head-v30 phase2-head-v31">
    <div>
        <span class="panel-eyebrow">Phase 5</span>
        <h2>SEO, social preview, schema and marketing tracking control.</h2>
        <p>Use this area to improve Google indexing, Facebook/WhatsApp share preview and conversion tracking without editing raw PHP.</p>
    </div>
    <div class="admin-list-actions-v30">
        <a class="button secondary" href="<?= e(page_url('sitemap')) ?>" target="_blank" rel="noopener">View Sitemap</a>
        <a class="button ghost" href="<?= e(page_url('robots')) ?>" target="_blank" rel="noopener">Robots.txt</a>
        <a class="button ghost" href="<?= e(base_url()) ?>" target="_blank" rel="noopener">Preview Site</a>
    </div>
</section>

<div class="phase5-score-v36">
    <article><strong><?= $score ?>%</strong><span>SEO readiness</span></article>
    <?php foreach($checks as $label => $ready): ?>
        <article><strong><?= $ready ? '✓' : '!' ?></strong><span><?= e($label) ?> <?= $ready ? 'ready' : 'needs input' ?></span></article>
    <?php endforeach; ?>
</div>

<form class="cms-form-v31" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="panel cms-section-v31">
        <div class="cms-section-title-v31"><span>01</span><div><h2>Global SEO</h2><p>Used across public pages, social sharing and search snippets.</p></div></div>
        <div class="form-grid enhanced-form-v30">
            <label>SEO Title Suffix<input name="global_seo_title_suffix" value="<?= e(cms_setting($settings,'global_seo_title_suffix')) ?>"></label>
            <label class="full">SEO Keywords<textarea name="global_seo_keywords" rows="3"><?= e(cms_setting($settings,'global_seo_keywords')) ?></textarea><small>Comma-separated keywords for basic search hints.</small></label>
            <label>Open Graph Image Path<input name="global_og_image" value="<?= e(cms_setting($settings,'global_og_image')) ?>"><small>Used when pages are shared on Facebook/WhatsApp.</small></label>
            <label>Upload OG Image<input type="file" name="global_og_file" accept=".jpg,.jpeg,.png,.webp"><small>Recommended 1200×630, max 3MB.</small></label>
        </div>
        <div class="form-grid enhanced-form-v30">
            <label class="full">Google Search Console verification token<input name="google_site_verification" value="<?= e(cms_setting($settings,'google_site_verification')) ?>" placeholder="Paste only the content value"><small>Use the HTML tag method in Search Console, paste its content token here, save, then click Verify in Google.</small></label>
        </div>
    </section>

    <section class="panel cms-section-v31">
        <div class="cms-section-title-v31"><span>02</span><div><h2>Organization Schema</h2><p>This creates structured data for Google and other crawlers.</p></div></div>
        <div class="form-grid enhanced-form-v30">
            <label>Organization Name<input name="organization_name" value="<?= e(cms_setting($settings,'organization_name')) ?>"></label>
            <label>Legal Name<input name="organization_legal_name" value="<?= e(cms_setting($settings,'organization_legal_name')) ?>"></label>
            <label>Logo Path<input name="organization_logo" value="<?= e(cms_setting($settings,'organization_logo')) ?>"></label>
            <label>Upload Logo<input type="file" name="organization_logo_file" accept=".jpg,.jpeg,.png,.webp"></label>
            <label>Phone<input name="organization_phone" value="<?= e(cms_setting($settings,'organization_phone') ?: ($settings['contact_phone'] ?? '')) ?>"></label>
            <label>Email<input type="email" name="organization_email" value="<?= e(cms_setting($settings,'organization_email') ?: ($settings['email'] ?? '')) ?>"></label>
            <label class="full">Address<textarea name="organization_address" rows="2"><?= e(cms_setting($settings,'organization_address') ?: ($settings['office_address_en'] ?? '')) ?></textarea></label>
            <label class="full">Organization Description<textarea name="organization_description" rows="4"><?= e(cms_setting($settings,'organization_description')) ?></textarea></label>
        </div>
    </section>

    <section class="panel cms-section-v31">
        <div class="cms-section-title-v31"><span>03</span><div><h2>Marketing Tracking</h2><p>Optional Google Analytics and Meta Pixel IDs.</p></div></div>
        <div class="form-grid enhanced-form-v30">
            <label>Google Analytics ID<input name="google_analytics_id" value="<?= e(cms_setting($settings,'google_analytics_id')) ?>" placeholder="G-XXXXXXXXXX"></label>
            <label>Meta Pixel ID<input name="meta_pixel_id" value="<?= e(cms_setting($settings,'meta_pixel_id')) ?>"></label>
            <label>Marketing CTA English<input name="marketing_cta_en" value="<?= e(cms_setting($settings,'marketing_cta_en')) ?>"></label>
            <label>Marketing CTA Bangla<input name="marketing_cta_bn" value="<?= e(cms_setting($settings,'marketing_cta_bn')) ?>"></label>
            <label class="full">Conversion Event Label<input name="conversion_event_label" value="<?= e(cms_setting($settings,'conversion_event_label')) ?>"></label>
        </div>
    </section>

    <section class="panel cms-section-v31">
        <div class="cms-section-title-v31"><span>04</span><div><h2>Phase 5 Checklist</h2><p>Quick actions for SEO and marketing readiness.</p></div></div>
        <div class="quick-action-grid">
            <a href="<?= e(page_url('sitemap')) ?>" target="_blank" rel="noopener"><b>Sitemap</b><span>Check that products and blogs are included.</span></a>
            <a href="<?= e(page_url('robots')) ?>" target="_blank" rel="noopener"><b>Robots</b><span>Confirm admin/config folders are blocked.</span></a>
            <a href="<?= e(page_url('blogs')) ?>" target="_blank" rel="noopener"><b>Blog SEO</b><span>Review titles, categories, dates and archive links.</span></a>
            <a href="products.php"><b>Product Schema</b><span>Complete product specs, gallery, FAQ and SEO fields.</span></a>
        </div>
    </section>

    <section class="panel cms-section-v31">
        <div class="cms-section-title-v31"><span>05</span><div><h2>Backlink Strategy</h2><p>Build relevant trust signals gradually; quality and relevance matter more than link count.</p></div></div>
        <div class="quick-action-grid">
            <div><b>Business profiles</b><span>Complete trusted Bangladesh trade, map and company profiles with consistent name, address, phone and homepage URL.</span></div>
            <div><b>Partner links</b><span>Ask manufacturers, distributors and verified retail partners to link to the matching brand or product page.</span></div>
            <div><b>Useful content</b><span>Publish original mosquito-awareness, safe-use, product and distribution articles that industry pages can cite.</span></div>
            <div><b>Quality control</b><span>Avoid paid link farms, automated comments, irrelevant directories and exact-match anchor spam.</span></div>
        </div>
    </section>

    <div class="sticky-save-v31 panel">
        <div><b>Ready to save Phase 5?</b><span>These settings affect SEO, schema and social share previews.</span></div>
        <button class="button" type="submit">Save SEO & Marketing</button>
    </div>
</form>
<?php admin_footer(); ?>

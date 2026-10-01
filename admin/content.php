<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase2_cms_schema();
$pdo = db();
$error = '';
$flash = '';
$coreKeys = ['site_name','contact_phone','whatsapp_number','email','facebook','office_address_en','office_address_bn','catalogue_url','catalogue_title_en','catalogue_title_bn','catalogue_version','catalogue_updated_at','catalogue_note_en','catalogue_note_bn','google_map_url','meta_pixel_id','google_analytics_id'];
$heroKeys = ['home_seo_title','home_seo_description','hero_pill_en','hero_pill_bn','hero_title_line1_en','hero_title_line1_bn','hero_title_line2_en','hero_title_line2_bn','hero_subtitle_en','hero_subtitle_bn','hero_primary_cta_en','hero_primary_cta_bn','hero_secondary_cta_en','hero_secondary_cta_bn'];
$aboutKeys = ['about_eyebrow_en','about_eyebrow_bn','about_title_en','about_title_bn','about_intro_en','about_intro_bn','mission_text_en','mission_text_bn','vision_text_en','vision_text_bn','action_plan_en','action_plan_bn','footer_description_en','footer_description_bn'];
$allKeys = array_merge($coreKeys, $heroKeys, $aboutKeys);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $uploadedCatalogue = upload_document_file('catalogue_file', 'uploads/catalogues', 'atoz-signal-catalogue', 8 * 1024 * 1024);
        if ($uploadedCatalogue) {
            $_POST['catalogue_url'] = $uploadedCatalogue;
        }
        save_site_settings($allKeys, $_POST);
        log_admin_action('content_cms_save', 'Content CMS settings were updated.');
        $flash = 'Content and site settings saved successfully.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
$settings = get_site_settings();
admin_header('Content CMS');
?>
<?php if($flash): ?><div class="success"><?= e($flash) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>

<section class="panel admin-list-head-v30 phase2-head-v31">
    <div>
        <span class="panel-eyebrow">Phase 2 CMS</span>
        <h2>Control homepage, about copy, footer, contact and tracking from one place.</h2>
        <p>This page moves key website content out of raw PHP files and into safe admin-managed settings.</p>
    </div>
    <div class="admin-list-actions-v30">
        <a class="button secondary" href="../index.php" target="_blank" rel="noopener">Preview Website</a>
        <a class="button ghost" href="team.php">Team</a>
        <a class="button ghost" href="timeline.php">Timeline</a>
        <a class="button ghost" href="seo.php">SEO & Marketing</a>
    </div>
</section>

<form class="cms-form-v31" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <section class="panel cms-section-v31">
        <div class="cms-section-title-v31"><span>01</span><div><h2>Core Business Information</h2><p>Used in header, footer, floating contact, enquiry messages and SEO basics.</p></div></div>
        <div class="form-grid enhanced-form-v30">
            <label>Site Name<input name="site_name" value="<?= e($settings['site_name'] ?? '') ?>"></label>
            <label>Contact Phone<input name="contact_phone" value="<?= e($settings['contact_phone'] ?? '') ?>"></label>
            <label>WhatsApp Number<input name="whatsapp_number" value="<?= e($settings['whatsapp_number'] ?? '') ?>"><small>Use country code without +. Example: 8801788609529</small></label>
            <label>Email<input type="email" name="email" value="<?= e($settings['email'] ?? '') ?>"></label>
            <label class="full">Facebook URL<input name="facebook" value="<?= e($settings['facebook'] ?? '') ?>"></label>
            <label class="full">Office Address English<textarea name="office_address_en" rows="2"><?= e($settings['office_address_en'] ?? '') ?></textarea></label>
            <label class="full">Office Address Bangla<textarea name="office_address_bn" rows="2"><?= e($settings['office_address_bn'] ?? '') ?></textarea></label>
            <label>Catalogue URL<input name="catalogue_url" value="<?= e($settings['catalogue_url'] ?? '') ?>"><small>Auto-filled when you upload a catalogue below.</small></label>
            <label>Upload Catalogue<input type="file" name="catalogue_file" accept=".pdf,.jpg,.jpeg,.png,.webp"><small>PDF/JPG/PNG/WEBP, max 8MB.</small></label>
            <label>Catalogue Title English<input name="catalogue_title_en" value="<?= e(cms_setting($settings,'catalogue_title_en')) ?>"></label>
            <label>Catalogue Title Bangla<input name="catalogue_title_bn" value="<?= e(cms_setting($settings,'catalogue_title_bn')) ?>"></label>
            <label>Catalogue Version<input name="catalogue_version" value="<?= e(cms_setting($settings,'catalogue_version')) ?>" placeholder="2026 Trade Edition"></label>
            <label>Catalogue Updated Date<input type="date" name="catalogue_updated_at" value="<?= e(cms_setting($settings,'catalogue_updated_at')) ?>"></label>
            <label class="full">Catalogue Note English<textarea name="catalogue_note_en" rows="2"><?= e(cms_setting($settings,'catalogue_note_en')) ?></textarea></label>
            <label class="full">Catalogue Note Bangla<textarea name="catalogue_note_bn" rows="2"><?= e(cms_setting($settings,'catalogue_note_bn')) ?></textarea></label>
            <label class="full">Google Map URL / Embed Link<input name="google_map_url" value="<?= e(cms_setting($settings,'google_map_url')) ?>"></label>
            <label>Google Analytics ID<input name="google_analytics_id" value="<?= e(cms_setting($settings,'google_analytics_id')) ?>" placeholder="G-XXXXXXXXXX"></label>
            <label>Meta Pixel ID<input name="meta_pixel_id" value="<?= e(cms_setting($settings,'meta_pixel_id')) ?>"></label>
        </div>
    </section>

    <section class="panel cms-section-v31">
        <div class="cms-section-title-v31"><span>02</span><div><h2>Homepage Hero + SEO</h2><p>Edit the main landing headline without opening index.php.</p></div></div>
        <div class="form-grid enhanced-form-v30">
            <label>Homepage SEO Title<input name="home_seo_title" value="<?= e(cms_setting($settings,'home_seo_title')) ?>"></label>
            <label>Homepage SEO Description<input name="home_seo_description" value="<?= e(cms_setting($settings,'home_seo_description')) ?>"></label>
            <label>Hero Pill English<input name="hero_pill_en" value="<?= e(cms_setting($settings,'hero_pill_en')) ?>"></label>
            <label>Hero Pill Bangla<input name="hero_pill_bn" value="<?= e(cms_setting($settings,'hero_pill_bn')) ?>"></label>
            <label>Hero Title Line 1 English<input name="hero_title_line1_en" value="<?= e(cms_setting($settings,'hero_title_line1_en')) ?>"></label>
            <label>Hero Title Line 1 Bangla<input name="hero_title_line1_bn" value="<?= e(cms_setting($settings,'hero_title_line1_bn')) ?>"></label>
            <label>Hero Title Line 2 English<input name="hero_title_line2_en" value="<?= e(cms_setting($settings,'hero_title_line2_en')) ?>"></label>
            <label>Hero Title Line 2 Bangla<input name="hero_title_line2_bn" value="<?= e(cms_setting($settings,'hero_title_line2_bn')) ?>"></label>
            <label class="full">Hero Subtitle English<textarea name="hero_subtitle_en" rows="3"><?= e(cms_setting($settings,'hero_subtitle_en')) ?></textarea></label>
            <label class="full">Hero Subtitle Bangla<textarea name="hero_subtitle_bn" rows="3"><?= e(cms_setting($settings,'hero_subtitle_bn')) ?></textarea></label>
            <label>Primary CTA English<input name="hero_primary_cta_en" value="<?= e(cms_setting($settings,'hero_primary_cta_en')) ?>"></label>
            <label>Primary CTA Bangla<input name="hero_primary_cta_bn" value="<?= e(cms_setting($settings,'hero_primary_cta_bn')) ?>"></label>
            <label>Secondary CTA English<input name="hero_secondary_cta_en" value="<?= e(cms_setting($settings,'hero_secondary_cta_en')) ?>"></label>
            <label>Secondary CTA Bangla<input name="hero_secondary_cta_bn" value="<?= e(cms_setting($settings,'hero_secondary_cta_bn')) ?>"></label>
        </div>
    </section>

    <section class="panel cms-section-v31">
        <div class="cms-section-title-v31"><span>03</span><div><h2>About, Mission, Vision and Footer Copy</h2><p>These texts power the About Us page and public footer.</p></div></div>
        <div class="form-grid enhanced-form-v30">
            <label>About Eyebrow English<input name="about_eyebrow_en" value="<?= e(cms_setting($settings,'about_eyebrow_en')) ?>"></label>
            <label>About Eyebrow Bangla<input name="about_eyebrow_bn" value="<?= e(cms_setting($settings,'about_eyebrow_bn')) ?>"></label>
            <label class="full">About Title English<textarea name="about_title_en" rows="2"><?= e(cms_setting($settings,'about_title_en')) ?></textarea></label>
            <label class="full">About Title Bangla<textarea name="about_title_bn" rows="2"><?= e(cms_setting($settings,'about_title_bn')) ?></textarea></label>
            <label class="full">About Intro English<textarea name="about_intro_en" rows="3"><?= e(cms_setting($settings,'about_intro_en')) ?></textarea></label>
            <label class="full">About Intro Bangla<textarea name="about_intro_bn" rows="3"><?= e(cms_setting($settings,'about_intro_bn')) ?></textarea></label>
            <label class="full">Mission English<textarea name="mission_text_en" rows="3"><?= e(cms_setting($settings,'mission_text_en')) ?></textarea></label>
            <label class="full">Mission Bangla<textarea name="mission_text_bn" rows="3"><?= e(cms_setting($settings,'mission_text_bn')) ?></textarea></label>
            <label class="full">Vision English<textarea name="vision_text_en" rows="3"><?= e(cms_setting($settings,'vision_text_en')) ?></textarea></label>
            <label class="full">Vision Bangla<textarea name="vision_text_bn" rows="3"><?= e(cms_setting($settings,'vision_text_bn')) ?></textarea></label>
            <label class="full">Action Plan English <small>One item per line.</small><textarea name="action_plan_en" rows="5"><?= e(cms_setting($settings,'action_plan_en')) ?></textarea></label>
            <label class="full">Action Plan Bangla <small>One item per line.</small><textarea name="action_plan_bn" rows="5"><?= e(cms_setting($settings,'action_plan_bn')) ?></textarea></label>
            <label class="full">Footer Description English<textarea name="footer_description_en" rows="3"><?= e(cms_setting($settings,'footer_description_en')) ?></textarea></label>
            <label class="full">Footer Description Bangla<textarea name="footer_description_bn" rows="3"><?= e(cms_setting($settings,'footer_description_bn')) ?></textarea></label>
        </div>
    </section>

    <div class="sticky-save-v31 panel">
        <div><b>Ready to save?</b><span>Changes will instantly affect frontend content.</span></div>
        <button class="button" type="submit">Save Content CMS</button>
    </div>
</form>
<?php admin_footer(); ?>

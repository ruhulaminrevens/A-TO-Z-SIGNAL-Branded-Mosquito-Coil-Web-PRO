<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase2_cms_schema();
ensure_catalogue_system_schema();
$settings = get_site_settings();
$pdo = db();
$catalogueDownloadCount = 0;
$catalogueDownloadWeek = 0;
try {
    $catalogueDownloadCount = (int)$pdo->query('SELECT COUNT(*) FROM catalogue_downloads')->fetchColumn();
    $catalogueDownloadWeek = (int)$pdo->query('SELECT COUNT(*) FROM catalogue_downloads WHERE downloaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
} catch (Throwable $e) {
    $catalogueDownloadCount = 0;
    $catalogueDownloadWeek = 0;
}
admin_header('Settings');
?>
<section class="panel admin-list-head-v30 phase2-head-v31">
    <div>
        <span class="panel-eyebrow">Settings Hub</span>
        <h2>Most site settings now live inside the Content CMS.</h2>
        <p>Use Content CMS for phone, WhatsApp, email, footer text, homepage hero, About Us copy, catalogue metadata, download link and tracking IDs.</p>
    </div>
    <div class="admin-list-actions-v30">
        <a class="button" href="content.php">Open Content CMS</a>
        <a class="button secondary" href="profile.php">Admin Profile</a>
    </div>
</section>
<section class="admin-grid two-col">
    <article class="panel admin-module">
        <div class="panel-title-row"><div><span class="panel-eyebrow">Current Contact</span><h2>Business Info</h2></div></div>
        <div class="control-map control-map-v31">
            <div><b>Phone</b><span><?= e($settings['contact_phone'] ?? '') ?></span></div>
            <div><b>WhatsApp</b><span><?= e($settings['whatsapp_number'] ?? '') ?></span></div>
            <div><b>Email</b><span><?= e($settings['email'] ?? '') ?></span></div>
            <div><b>Catalogue</b><span><?= e($settings['catalogue_url'] ?? '') ?></span></div>
            <div><b>Catalogue Version</b><span><?= e($settings['catalogue_version'] ?? '') ?></span></div>
            <div><b>Catalogue Downloads</b><span><?= $catalogueDownloadCount ?> total · <?= $catalogueDownloadWeek ?> this week</span></div>
        </div>
    </article>
    <article class="panel admin-module">
        <div class="panel-title-row"><div><span class="panel-eyebrow">Phase 2 Areas</span><h2>Editable Sections</h2></div></div>
        <div class="quick-action-grid phase2-quick-v31">
            <a href="content.php"><b>Content CMS</b><span>Homepage, About copy, footer, tracking and catalogue.</span></a>
            <a href="team.php"><b>Leadership Team</b><span>Manage About Us photos, roles and descriptions.</span></a>
            <a href="timeline.php"><b>Company Timeline</b><span>Manage sales meets, launches and archive milestones.</span></a>
            <a href="../index.php" target="_blank" rel="noopener"><b>Preview Website</b><span>Check frontend after changes.</span></a>
        </div>
    </article>
</section>
<?php admin_footer(); ?>

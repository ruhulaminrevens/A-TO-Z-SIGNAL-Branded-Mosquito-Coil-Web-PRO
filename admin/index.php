<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_blog_table();
ensure_enquiry_crm_columns();
ensure_enquiry_notes_schema();
ensure_phase2_cms_schema();
ensure_catalogue_system_schema();
ensure_phase43_media_content_schema();
$pdo = db();

function admin_count(PDO $pdo, string $sql): int {
    try { return (int)$pdo->query($sql)->fetchColumn(); } catch (Throwable $e) { return 0; }
}

function admin_rows(PDO $pdo, string $sql): array {
    try { return $pdo->query($sql)->fetchAll(); } catch (Throwable $e) { return []; }
}

function admin_wa_link(string $phone): string {
    $digits = preg_replace('/\D+/', '', $phone);
    if ($digits === '') return '#';
    if (strlen($digits) === 11 && substr($digits, 0, 1) === '0') $digits = '88' . $digits;
    if (strlen($digits) === 10 && substr($digits, 0, 1) === '1') $digits = '880' . $digits;
    return 'https://wa.me/' . $digits;
}

function admin_percent(int $value, int $total): int {
    if ($total <= 0) return 0;
    return (int)round(($value / $total) * 100);
}

$productCount = admin_count($pdo, 'SELECT COUNT(*) FROM products');
$activeCount = admin_count($pdo, 'SELECT COUNT(*) FROM products WHERE is_active=1');
$hiddenCount = max(0, $productCount - $activeCount);
$atozCount = admin_count($pdo, "SELECT COUNT(*) FROM products WHERE brand='A TO Z'");
$signalCount = admin_count($pdo, "SELECT COUNT(*) FROM products WHERE brand='SIGNAL'");
$enquiryCount = admin_count($pdo, 'SELECT COUNT(*) FROM distributor_enquiries');
$todayEnquiries = admin_count($pdo, 'SELECT COUNT(*) FROM distributor_enquiries WHERE DATE(created_at)=CURDATE()');
$weekEnquiries = admin_count($pdo, 'SELECT COUNT(*) FROM distributor_enquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
$dueEnquiries = admin_count($pdo, "SELECT COUNT(*) FROM distributor_enquiries WHERE follow_up_at IS NOT NULL AND follow_up_at <= CURDATE() AND " . enquiry_active_status_condition('status'));
$activeEnquiries = admin_count($pdo, 'SELECT COUNT(*) FROM distributor_enquiries WHERE ' . enquiry_active_status_condition('status'));
$convertedEnquiries = admin_count($pdo, "SELECT COUNT(*) FROM distributor_enquiries WHERE status='converted'");
$conversionRate = $enquiryCount > 0 ? round(($convertedEnquiries / $enquiryCount) * 100, 1) : 0;
$duplicateLeadGroups = count(enquiry_duplicate_phone_groups($pdo, 20));
$notesTotal = admin_count($pdo, 'SELECT COUNT(*) FROM enquiry_notes');
$pipelineCounts = enquiry_status_counts($pdo);
$dashboardDueLeads = admin_rows($pdo, "SELECT * FROM distributor_enquiries WHERE follow_up_at IS NOT NULL AND follow_up_at <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND " . enquiry_active_status_condition('status') . " ORDER BY follow_up_at ASC, id DESC LIMIT 5");
$productLeadCount = admin_count($pdo, "SELECT COUNT(*) FROM distributor_enquiries WHERE enquiry_type='Product Enquiry'");
$catalogueDownloads = admin_count($pdo, 'SELECT COUNT(*) FROM catalogue_downloads');
$catalogueDownloadsWeek = admin_count($pdo, 'SELECT COUNT(*) FROM catalogue_downloads WHERE downloaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
$mediaAssetCount = admin_count($pdo, 'SELECT COUNT(*) FROM media_assets');
$contentBlockCount = admin_count($pdo, 'SELECT COUNT(*) FROM content_blocks');
$blogCount = admin_count($pdo, 'SELECT COUNT(*) FROM blog_posts');
$publishedBlogCount = admin_count($pdo, 'SELECT COUNT(*) FROM blog_posts WHERE is_published=1');
$draftBlogCount = max(0, $blogCount - $publishedBlogCount);

$latestEnquiries = admin_rows($pdo, 'SELECT * FROM distributor_enquiries ORDER BY id DESC LIMIT 8');
$recentProducts = admin_rows($pdo, 'SELECT * FROM products ORDER BY updated_at DESC, id DESC LIMIT 6');
$recentBlogs = admin_rows($pdo, 'SELECT * FROM blog_posts ORDER BY COALESCE(updated_at, created_at) DESC, id DESC LIMIT 5');

$settings = get_site_settings();
$requiredSettings = ['site_name','contact_phone','whatsapp_number','email','facebook','office_address_en','office_address_bn','catalogue_url'];
$missingSettings = [];
foreach ($requiredSettings as $key) {
    if (trim((string)($settings[$key] ?? '')) === '') $missingSettings[] = $key;
}
$settingsScore = admin_percent(count($requiredSettings) - count($missingSettings), count($requiredSettings));

$uploadChecks = [
    'Product images' => __DIR__ . '/../uploads/products',
    'Blog images' => __DIR__ . '/../uploads/blog',
    'Catalogues' => __DIR__ . '/../uploads/catalogues',
];

admin_header('Dashboard');
?>
<section class="dashboard-hero panel">
    <div>
        <span class="hero-label">Admin Command Center</span>
        <h2>Manage the full website without opening raw code.</h2>
        <p>Products, specs, galleries, FAQs, product-specific enquiries, catalogue downloads, distributor leads, pipeline reminders, archive/blog posts and site settings are now organized from one practical dashboard.</p>
        <div class="hero-actions">
            <a class="button" href="product-form.php">+ Add Product</a>
            <a class="button secondary" href="blog-form.php">+ Add Blog Post</a>
            <a class="button ghost" href="content.php">Content CMS</a>
            <a class="button ghost" href="seo.php">SEO & Marketing</a>
            <a class="button ghost" href="profile.php">Edit Profile</a>
        </div>
        <p class="dashboard-actions-note">Hint: Use quick actions below for regular work; use Settings/Profile only when site or admin information changes.</p>
    </div>
    <div class="hero-health-card" aria-label="Website setup score">
        <span>Setup Health</span>
        <strong><?= $settingsScore ?>%</strong>
        <em><?= count($missingSettings) ? count($missingSettings) . ' setting(s) need attention' : 'Core settings complete' ?></em>
        <a href="settings.php">Review settings →</a>
    </div>
</section>

<section class="metric-grid" aria-label="Dashboard summary">
    <article class="metric-card red"><span>Total Products</span><strong><?= $productCount ?></strong><em><?= $activeCount ?> active · <?= $hiddenCount ?> hidden</em></article>
    <article class="metric-card green"><span>Lead Enquiries</span><strong><?= $enquiryCount ?></strong><em><?= $todayEnquiries ?> today · <?= $weekEnquiries ?> this week · <?= $productLeadCount ?> product</em></article>
    <article class="metric-card gold"><span>Blog / Archive</span><strong><?= $blogCount ?></strong><em><?= $publishedBlogCount ?> published · <?= $draftBlogCount ?> draft</em></article>
    <article class="metric-card blue"><span>Catalogue Downloads</span><strong><?= $catalogueDownloads ?></strong><em><?= $catalogueDownloadsWeek ?> this week · tracked</em></article>
    <article class="metric-card gold"><span>Media & Blocks</span><strong><?= $mediaAssetCount ?></strong><em><?= $contentBlockCount ?> reusable block(s) · v43 CMS</em></article>
    <article class="metric-card blue"><span>Brand Split</span><strong><?= $atozCount ?> / <?= $signalCount ?></strong><em>A TO Z / SIGNAL products</em></article>
    <article class="metric-card red"><span>CRM Pipeline</span><strong><?= $activeEnquiries ?></strong><em><?= $dueEnquiries ?> due · <?= $conversionRate ?>% converted · <?= $duplicateLeadGroups ?> duplicate groups</em></article>
</section>

<section class="dashboard-crm-v41">
    <div class="dashboard-crm-grid-v41">
        <div class="panel admin-module">
            <div class="panel-title-row"><div><span class="panel-eyebrow">Lead Pipeline</span><h2>CRM Stage Snapshot</h2></div><a class="small-link" href="enquiries.php">Open CRM →</a></div>
            <div class="dashboard-pipeline-bars-v41">
                <?php foreach(enquiry_status_options() as $key => $label): $count = (int)($pipelineCounts[$key] ?? 0); $pct = $enquiryCount > 0 ? min(100, round(($count / $enquiryCount) * 100)) : 0; ?>
                    <a href="enquiries.php?status=<?= e($key) ?>"><b><?= e($label) ?></b><span><i style="width:<?= (int)$pct ?>%"></i></span><em><?= $count ?></em></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="panel admin-module">
            <div class="panel-title-row"><div><span class="panel-eyebrow">Follow-up</span><h2>Due / Upcoming Leads</h2></div><a class="small-link" href="enquiries.php?follow=due">Due only →</a></div>
            <div class="dashboard-reminder-list-v41">
                <?php foreach($dashboardDueLeads as $lead): ?>
                    <a href="enquiries.php?q=<?= urlencode((string)$lead['phone']) ?>"><b><?= e($lead['name']) ?></b><span><?= e((string)$lead['follow_up_at']) ?> · <?= e($lead['district']) ?> · <?= e(enquiry_status_label($lead['status'] ?? 'new')) ?></span></a>
                <?php endforeach; ?>
                <?php if(!$dashboardDueLeads): ?><p>No due reminders right now.</p><?php endif; ?>
                <small><?= $notesTotal ?> saved CRM note(s) · <?= $duplicateLeadGroups ?> duplicate phone group(s)</small>
            </div>
        </div>
    </div>
</section>

<section class="admin-grid two-col">
    <div class="panel admin-module">
        <div class="panel-title-row">
            <div><span class="panel-eyebrow">Fast Work</span><h2>Quick Actions</h2></div>
        </div>
        <div class="quick-action-grid">
            <a href="products.php" data-tip="Tip: Drag/order products from this area after updating images."><b>Manage Products</b><span>Edit product names, Bangla copy, images, order and visibility.</span></a>
            <a href="product-form.php" data-tip="Tip: Use clean PNG/WebP photos for best frontend quality."><b>Add New Product</b><span>Upload image, fill specs, FAQ and publish instantly.</span></a>
            <a href="enquiries.php" data-tip="Tip: Start from due follow-ups, then move leads across the pipeline."><b>Lead CRM Pipeline</b><span>View pipeline board, reminders, notes, duplicate phones and analytics.</span></a>
            <a href="blogs.php" data-tip="Tip: Keep recent sales meets and launch photos published."><b>Manage Blog / Archive</b><span>Edit company events, archive posts and images.</span></a>
            <a href="media.php" data-tip="Tip: Sync uploads after deployment so old images appear in the library."><b>Media Library</b><span>Upload, optimize, index and reuse images across pages.</span></a>
            <a href="content-blocks.php" data-tip="Tip: Use blocks for campaign CTAs and blog shortcodes."><b>Content Blocks</b><span>Create reusable homepage and blog content blocks.</span></a>
            <a href="content.php" data-tip="Tip: Update hero text, About copy and contact details from here."><b>Content CMS</b><span>Update homepage, About page, footer, contact info and catalogue.</span></a>
            <a href="team.php" data-tip="Tip: Keep leadership photos and roles updated."><b>Leadership Team</b><span>Edit About Us leader photos, roles and descriptions.</span></a>
            <a href="timeline.php" data-tip="Tip: Add sales meets, launches and milestones here."><b>Company Timeline</b><span>Manage About Us archive milestones and photo cards.</span></a>
            <a href="profile.php" data-tip="Tip: Change password regularly and keep username private."><b>Admin Profile</b><span>Change admin photo, name, username and password securely.</span></a>
            <a href="seo.php" data-tip="Tip: Update OG image and schema before running ads."><b>SEO & Marketing</b><span>Control schema, social preview, sitemap and tracking IDs.</span></a>
            <a href="../index.php" target="_blank" rel="noopener" data-tip="Tip: Preview after each product, blog or settings update."><b>Preview Website</b><span>Open the live frontend in a new tab.</span></a>
        </div>
    </div>

    <div class="panel admin-module">
        <div class="panel-title-row">
            <div><span class="panel-eyebrow">Website Readiness</span><h2>Control Checklist</h2></div>
        </div>
        <div class="checklist">
            <div class="<?= $activeCount ? 'ok' : 'warn' ?>"><span></span><b>Active products</b><em><?= $activeCount ? $activeCount . ' product(s) visible' : 'No active product found' ?></em></div>
            <div class="<?= $publishedBlogCount ? 'ok' : 'warn' ?>"><span></span><b>Archive/blog posts</b><em><?= $publishedBlogCount ? $publishedBlogCount . ' published post(s)' : 'No published blog post' ?></em></div>
            <div class="<?= count($missingSettings) ? 'warn' : 'ok' ?>"><span></span><b>Core settings</b><em><?= count($missingSettings) ? 'Missing: ' . e(implode(', ', $missingSettings)) : 'All key settings are filled' ?></em></div>
            <?php foreach($uploadChecks as $label => $path): $ready = is_dir($path) && is_writable($path); ?>
                <div class="<?= $ready ? 'ok' : 'warn' ?>"><span></span><b><?= e($label) ?></b><em><?= $ready ? 'Upload folder is ready' : 'Folder permission needs checking' ?></em></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="admin-grid two-col wide-left">
    <div class="panel admin-module">
        <div class="panel-title-row">
            <div><span class="panel-eyebrow">Latest Leads</span><h2>Distributor Enquiries</h2></div>
            <a class="small-link" href="enquiries.php">View all →</a>
        </div>
        <label class="table-search-label">Search latest enquiries
            <input class="table-search" type="search" placeholder="Search name, phone, district..." data-table-filter="#latestEnquiriesTable">
        </label>
        <div class="table-wrap responsive-cards">
            <table id="latestEnquiriesTable">
                <thead><tr><th>Name</th><th>Phone</th><th>District</th><th>Business</th><th>Interest</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach($latestEnquiries as $row): ?>
                    <tr>
                        <td data-label="Name"><strong><?= e($row['name']) ?></strong><?php if(!empty($row['company_name'])): ?><br><small><?= e($row['company_name']) ?></small><?php endif; ?></td>
                        <td data-label="Phone"><a href="tel:<?= e(preg_replace('/\s+/', '', $row['phone'])) ?>"><?= e($row['phone']) ?></a></td>
                        <td data-label="District"><?= e($row['district']) ?></td>
                        <td data-label="Business"><?= e($row['business_type']) ?><br><small class="status-pill-v30 <?= e($row['status'] ?? 'new') ?>"><?= e(enquiry_status_label($row['status'] ?? 'new')) ?></small></td>
                        <td data-label="Interest"><small><?= e($row['interested_brand'] ?? 'Both') ?> · <?= e($row['enquiry_type'] ?? 'Distributor') ?></small><?php if(!empty($row['product_name'])): ?><br><small><?= e($row['product_name']) ?></small><?php endif; ?><?php if(!empty($row['lead_source'])): ?><br><small><?= e($row['lead_source']) ?></small><?php endif; ?></td>
                        <td data-label="Date"><small><?= e((string)$row['created_at']) ?></small></td>
                        <td data-label="Action"><a class="mini-button" href="<?= e(whatsapp_url($row['phone'], lead_whatsapp_message($row))) ?>" target="_blank" rel="noopener">WhatsApp</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if(!$latestEnquiries): ?><tr><td colspan="7">No enquiries yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel admin-module">
        <div class="panel-title-row">
            <div><span class="panel-eyebrow">Content Status</span><h2>Recent Products</h2></div>
            <a class="small-link" href="products.php">Manage →</a>
        </div>
        <div class="recent-list">
            <?php foreach($recentProducts as $p): ?>
                <a href="product-form.php?id=<?= (int)$p['id'] ?>">
                    <img src="../<?= e($p['image']) ?>" alt="">
                    <span><b><?= e($p['name_en']) ?></b><em><?= e($p['brand']) ?> · <?= $p['is_active'] ? 'Active' : 'Hidden' ?></em></span>
                </a>
            <?php endforeach; ?>
            <?php if(!$recentProducts): ?><p>No products found.</p><?php endif; ?>
        </div>
    </div>
</section>

<section class="admin-grid two-col">
    <div class="panel admin-module">
        <div class="panel-title-row">
            <div><span class="panel-eyebrow">Archive</span><h2>Recent Blog Posts</h2></div>
            <a class="small-link" href="blogs.php">Edit archive →</a>
        </div>
        <div class="blog-mini-list">
            <?php foreach($recentBlogs as $post): ?>
                <a href="blog-form.php?id=<?= (int)$post['id'] ?>">
                    <span><?= $post['is_published'] ? 'Published' : 'Draft' ?></span>
                    <b><?= e($post['title_en']) ?></b>
                    <em><?= e((string)($post['published_at'] ?? 'No date')) ?></em>
                </a>
            <?php endforeach; ?>
            <?php if(!$recentBlogs): ?><p>No blog posts found.</p><?php endif; ?>
        </div>
    </div>

    <div class="panel admin-module">
        <div class="panel-title-row">
            <div><span class="panel-eyebrow">Frontend Controls</span><h2>Editable From Admin</h2></div>
        </div>
        <div class="control-map">
            <div><b>Homepage Product Cards</b><span>Products → Edit / order / hide-show</span></div>
            <div><b>Product Details Pages</b><span>Products → Specs, gallery, FAQ, usage/safety and SEO</span></div>
            <div><b>Distributor Form Leads</b><span>Enquiries → Pipeline, follow-ups, notes history, duplicate detection</span></div>
            <div><b>Blog / Event Archive</b><span>Blog / Archive → Add, edit, publish or draft</span></div>
            <div><b>Contact Information</b><span>Content CMS → Phone, WhatsApp, email, address</span></div>
            <div><b>Homepage Hero</b><span>Content CMS → Headline, CTA and SEO copy</span></div>
            <div><b>About Us Content</b><span>Content CMS / Team / Timeline → Editable page sections</span></div>
            <div><b>Catalogue System</b><span>Content CMS/Product Form → Global or product-specific catalogue URL</span></div>
            <div><b>Product Comparison</b><span>Products → Pack size, smoke type, hours and comparison highlight</span></div>
            <div><b>SEO & Marketing</b><span>SEO & Marketing → Schema, OG image, sitemap and tracking</span></div>
            <div><b>Admin Account</b><span>Admin Profile → Photo, username and password</span></div>
        </div>
    </div>
</section>
<?php admin_footer(); ?>

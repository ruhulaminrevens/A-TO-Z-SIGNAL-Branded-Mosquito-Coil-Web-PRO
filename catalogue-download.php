<?php
require_once __DIR__ . '/includes/db.php';
apply_security_headers();
ensure_phase2_cms_schema();
ensure_phase3_product_schema();
ensure_catalogue_system_schema();
$settings = get_site_settings();
$slug = trim((string)($_GET['product'] ?? ''));
$source = sanitize_lead_source($_GET['source'] ?? 'Catalogue');
$product = $slug !== '' ? get_product_by_slug($slug) : null;
$target = catalogue_target_url($settings, $product);
record_catalogue_download($product, $source, $target);
if ($target === '' || $target === '#') {
    $target = home_url('contact');
}
header('Location: ' . $target, true, 302);
exit;

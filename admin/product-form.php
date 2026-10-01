<?php
require_once __DIR__ . '/_layout.php';
require_admin();
ensure_phase3_product_schema();
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$product = null;
if ($id) { $stmt=$pdo->prepare('SELECT * FROM products WHERE id=?'); $stmt->execute([$id]); $product=$stmt->fetch(); }
$error='';

function product_form_gallery_text(?array $product): string {
    if (!$product) return '';
    $items = normalize_json_list($product['gallery_json'] ?? '');
    return implode("\n", $items);
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        verify_csrf();
        $brand = ($_POST['brand'] ?? '') === 'SIGNAL' ? 'SIGNAL' : 'A TO Z';
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameBn = trim($_POST['name_bn'] ?? '');
        if ($nameEn==='') throw new RuntimeException('English product name is required.');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($nameEn);
        $featuresRaw = trim($_POST['features'] ?? '');
        $features = json_encode(array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $featuresRaw)))), JSON_UNESCAPED_UNICODE);
        $slugCheck = $pdo->prepare('SELECT id FROM products WHERE slug = ? AND id <> ? LIMIT 1');
        $slugCheck->execute([$slug, $id]);
        if ($slugCheck->fetch()) throw new RuntimeException('This product slug is already used. Please choose another slug.');

        $imagePath = $product['image'] ?? '';
        $uploadedImage = upload_image_file('image', 'uploads/products', $slug, 2 * 1024 * 1024);
        if ($uploadedImage) $imagePath = $uploadedImage;
        if ($imagePath==='') throw new RuntimeException('Product image is required.');

        $galleryPaths = normalize_json_list($_POST['gallery_paths'] ?? '');
        $galleryUploads = upload_multiple_image_files('gallery_images', 'uploads/products', $slug, 2 * 1024 * 1024);
        $gallery = array_values(array_unique(array_filter(array_merge($galleryPaths, $galleryUploads), static fn($path) => trim((string)$path) !== '')));
        $galleryJson = $gallery ? json_encode($gallery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

        $faqItems = parse_faq_text((string)($_POST['faq_text'] ?? ''));
        $faqJson = $faqItems ? json_encode($faqItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $productCatalogueUrl = trim((string)($_POST['catalogue_url'] ?? ''));
        $comparisonHighlight = trim((string)($_POST['comparison_highlight'] ?? ''));

        $data = [
            $brand,
            $nameEn,
            $nameBn,
            $slug,
            trim($_POST['category'] ?? 'Mosquito Coil'),
            trim($_POST['protection_hours'] ?? ''),
            trim($_POST['pack_size'] ?? ''),
            trim($_POST['carton_size'] ?? ''),
            trim($_POST['smoke_type'] ?? ''),
            trim($_POST['fragrance'] ?? ''),
            trim($_POST['room_size'] ?? ''),
            trim($_POST['formula_type'] ?? ''),
            trim($_POST['burn_time'] ?? ''),
            trim($_POST['badge'] ?? ''),
            trim($_POST['short_description_en'] ?? ''),
            trim($_POST['short_description_bn'] ?? ''),
            trim($_POST['usage_instructions_en'] ?? ''),
            trim($_POST['usage_instructions_bn'] ?? ''),
            trim($_POST['safety_instructions_en'] ?? ''),
            trim($_POST['safety_instructions_bn'] ?? ''),
            $features,
            $imagePath,
            $galleryJson,
            $faqJson,
            $productCatalogueUrl,
            $comparisonHighlight,
            trim($_POST['accent_color'] ?? '#f5a623'),
            trim($_POST['seo_title'] ?? ''),
            trim($_POST['seo_description'] ?? ''),
            (int)($_POST['sort_order'] ?? 0),
            isset($_POST['is_active'])?1:0
        ];
        if ($id) {
            $data[]=$id;
            $stmt=$pdo->prepare('UPDATE products SET brand=?, name_en=?, name_bn=?, slug=?, category=?, protection_hours=?, pack_size=?, carton_size=?, smoke_type=?, fragrance=?, room_size=?, formula_type=?, burn_time=?, badge=?, short_description_en=?, short_description_bn=?, usage_instructions_en=?, usage_instructions_bn=?, safety_instructions_en=?, safety_instructions_bn=?, features=?, image=?, gallery_json=?, faq_json=?, catalogue_url=?, comparison_highlight=?, accent_color=?, seo_title=?, seo_description=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?');
            $stmt->execute($data);
        } else {
            $stmt=$pdo->prepare('INSERT INTO products (brand,name_en,name_bn,slug,category,protection_hours,pack_size,carton_size,smoke_type,fragrance,room_size,formula_type,burn_time,badge,short_description_en,short_description_bn,usage_instructions_en,usage_instructions_bn,safety_instructions_en,safety_instructions_bn,features,image,gallery_json,faq_json,catalogue_url,comparison_highlight,accent_color,seo_title,seo_description,sort_order,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
            $stmt->execute($data);
        }
        log_admin_action('product_save', 'Product ' . ($id ? ('ID ' . $id . ' updated') : 'created') . '.');
        header('Location: products.php'); exit;
    } catch (Throwable $e) { $error=$e->getMessage(); }
}
$featuresText = $product ? implode("\n", json_features($product['features'])) : '';
$faqText = $product ? faqs_to_text($product['faq_json'] ?? '') : '';
$galleryText = product_form_gallery_text($product);
admin_header($id ? 'Edit Product' : 'Add Product');
?>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form class="panel form-grid enhanced-form-v30 product-form-v32" method="post" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="form-intro-v30 full"><span>v40 Product Experience System</span><p>Control specs, comparison highlights, product catalogue links, gallery, FAQ schema content and SEO from one place.</p></div>

<div class="phase3-form-section-v32 full"><b>01</b><span>Basic product identity</span></div>
<label>Brand<select name="brand"><option <?= ($product['brand'] ?? '')==='A TO Z'?'selected':'' ?>>A TO Z</option><option <?= ($product['brand'] ?? '')==='SIGNAL'?'selected':'' ?>>SIGNAL</option></select><small>Select the brand family shown on frontend cards.</small></label>
<label>Name English<input name="name_en" value="<?= e($product['name_en'] ?? '') ?>" required><small>Main product title for English mode.</small></label>
<label>Name Bangla<input name="name_bn" value="<?= e($product['name_bn'] ?? '') ?>"><small>Bangla title for BN mode.</small></label>
<label>Slug<input name="slug" value="<?= e($product['slug'] ?? '') ?>" placeholder="auto-generated if blank"><small>Used in product details URL. Keep it unique.</small></label>
<label>Category<input name="category" value="<?= e($product['category'] ?? 'Mosquito Coil') ?>"></label>
<label>Badge<input name="badge" value="<?= e($product['badge'] ?? '') ?>" placeholder="Turbo Action / Best Seller"></label>
<label>Accent Color<input type="color" name="accent_color" value="<?= e($product['accent_color'] ?? '#f5a623') ?>"></label>
<label>Sort Order<input type="number" name="sort_order" value="<?= e((string)($product['sort_order'] ?? 0)) ?>"></label>

<div class="phase3-form-section-v32 full"><b>02</b><span>Product specification table</span></div>
<label>Protection Hours<input name="protection_hours" value="<?= e($product['protection_hours'] ?? '12H') ?>" placeholder="12H"></label>
<label>Pack Size<input name="pack_size" value="<?= e($product['pack_size'] ?? '') ?>" placeholder="10 pcs / 30 pcs / 60 pcs"></label>
<label>Carton Size<input name="carton_size" value="<?= e($product['carton_size'] ?? '') ?>" placeholder="Ctn / master carton info"></label>
<label>Smoke Type<input name="smoke_type" value="<?= e($product['smoke_type'] ?? '') ?>" placeholder="Micro Smoke / Low Smoke / Regular"></label>
<label>Fragrance<input name="fragrance" value="<?= e($product['fragrance'] ?? '') ?>" placeholder="Pleasant / Neem / Regular"></label>
<label>Room Size<input name="room_size" value="<?= e($product['room_size'] ?? '') ?>" placeholder="Bedroom / family room / large room"></label>
<label>Formula Type<input name="formula_type" value="<?= e($product['formula_type'] ?? '') ?>" placeholder="Turbo action / low-smoke formula"></label>
<label>Burning<input name="burn_time" value="<?= e($product['burn_time'] ?? '') ?>" placeholder="Steady burn / long-lasting"></label>
<label class="full">Comparison Highlight<input name="comparison_highlight" value="<?= e($product['comparison_highlight'] ?? '') ?>" maxlength="180" placeholder="Example: Best for bigger rooms / micro-smoke comfort"><small>Shown in homepage comparison table and product comparison blocks.</small></label>

<div class="phase3-form-section-v32 full"><b>03</b><span>Public product content</span></div>
<label class="full">Short Description English<textarea name="short_description_en" rows="3"><?= e($product['short_description_en'] ?? '') ?></textarea></label>
<label class="full">Short Description Bangla<textarea name="short_description_bn" rows="3"><?= e($product['short_description_bn'] ?? '') ?></textarea></label>
<label class="full">Features / Tags <small>One per line or comma separated. First two tags appear on product cards.</small><textarea name="features" rows="5"><?= e($featuresText) ?></textarea></label>
<label class="full">Usage Instructions English<textarea name="usage_instructions_en" rows="3" placeholder="How to use this product safely and clearly."><?= e($product['usage_instructions_en'] ?? '') ?></textarea></label>
<label class="full">Usage Instructions Bangla<textarea name="usage_instructions_bn" rows="3"><?= e($product['usage_instructions_bn'] ?? '') ?></textarea></label>
<label class="full">Safety Instructions English<textarea name="safety_instructions_en" rows="3" placeholder="Safety warning and responsible use instruction."><?= e($product['safety_instructions_en'] ?? '') ?></textarea></label>
<label class="full">Safety Instructions Bangla<textarea name="safety_instructions_bn" rows="3"><?= e($product['safety_instructions_bn'] ?? '') ?></textarea></label>
<label class="full">FAQ <small>Use one blank line between items. Format: Question line, then answer line. Or use Question | Answer.</small><textarea name="faq_text" rows="7" placeholder="How long does it protect?&#10;Up to 12 hours depending on room airflow and usage.&#10;&#10;Is it suitable for family use?&#10;Use only as directed and keep away from children."><?= e($faqText) ?></textarea></label>

<div class="phase3-form-section-v32 full"><b>04</b><span>Images and SEO</span></div>
<label>Product Image<input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" data-image-preview="#productImagePreview"><small>JPG, PNG or WEBP. Maximum 2MB.</small></label>
<div class="image-preview-box-v30"><img id="productImagePreview" class="preview" src="<?= !empty($product['image']) ? '../' . e($product['image']) : '' ?>" alt="" <?= empty($product['image']) ? 'hidden' : '' ?>><small>Main image preview</small></div>
<label class="full">Gallery Image Paths <small>One path per line. Example: uploads/products/example.webp</small><textarea name="gallery_paths" rows="4"><?= e($galleryText) ?></textarea></label>
<label class="full">Upload More Gallery Images<input type="file" name="gallery_images[]" accept=".jpg,.jpeg,.png,.webp" multiple><small>Optional. New files will be added to gallery paths automatically.</small></label>
<label class="full">Product Catalogue URL<input name="catalogue_url" value="<?= e($product['catalogue_url'] ?? '') ?>" placeholder="Optional product-specific catalogue PDF/image URL"><small>If blank, the global catalogue from Content CMS will be used.</small></label>
<label class="full">SEO Title<input name="seo_title" value="<?= e($product['seo_title'] ?? '') ?>" placeholder="Optional custom product SEO title"></label>
<label class="full">SEO Description<textarea name="seo_description" rows="3" placeholder="Optional custom meta description for this product."><?= e($product['seo_description'] ?? '') ?></textarea></label>
<label class="check"><input type="checkbox" name="is_active" <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>> Active / visible</label>
<div class="full form-submit-row-v30"><button class="button" type="submit">Save Product</button><a class="button secondary" href="products.php">Cancel</a><a class="button ghost" href="<?= e($product ? product_url($product) : home_url('products')) ?>" target="_blank" rel="noopener">Preview</a></div>
</form>
<?php admin_footer(); ?>

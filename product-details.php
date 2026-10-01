<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/site-ui.php';
ensure_phase3_product_schema();
$slug = trim((string)($_GET['slug'] ?? ''));
if ($slug === '') {
    $path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if (preg_match('~^product/([^/]+)$~', $path, $m)) $slug = rawurldecode($m[1]);
}
$product = $slug ? get_product_by_slug($slug) : null;
if (!$product) { require __DIR__ . '/404.php'; exit; }
$settings = get_site_settings();
$products = get_products(true);
$districts = district_list();
$features = json_features($product['features']);
$specs = product_specs($product);
$gallery = product_gallery($product);
$faqs = product_faqs($product);
$related = related_products($product, 3);
$comparisonRows = product_comparison_rows(array_slice(array_merge([$product], $related), 0, 4));
$catalogueUrl = catalogue_download_url($settings, $product, 'Product Page');
$seoTitle = trim((string)($product['seo_title'] ?? '')) ?: ($product['name_en'] . ' | Product Details');
$seoDescription = trim((string)($product['seo_description'] ?? '')) ?: trim((string)($product['short_description_en'] ?? ''));
$usageEn = trim((string)($product['usage_instructions_en'] ?? '')) ?: product_default_usage($product);
$usageBn = trim((string)($product['usage_instructions_bn'] ?? '')) ?: 'কয়েলটি নিরাপদ স্ট্যান্ডে রাখুন, সতর্কভাবে আগুন ধরান, শিশু ও দাহ্য বস্তু থেকে দূরে রাখুন এবং বাতাস চলাচল করে এমন ঘরে ব্যবহার করুন।';
$safetyEn = trim((string)($product['safety_instructions_en'] ?? '')) ?: product_default_safety($product);
$safetyBn = trim((string)($product['safety_instructions_bn'] ?? '')) ?: 'শুধু নির্দেশনা অনুযায়ী ব্যবহার করুন। খাবার, পর্দা, কাগজ, বিছানা ও সরাসরি স্পর্শ থেকে দূরে রাখুন। ব্যবহারের পর হাত ধুয়ে নিন এবং শিশুদের নাগালের বাইরে রাখুন।';
if (!$faqs) {
    $faqs = [
        ['q' => 'How long does this product protect?', 'a' => 'Protection performance depends on airflow, room size and correct usage. Follow the product pack instruction for best results.'],
        ['q' => 'Can I request distributor support?', 'a' => 'Yes. Use the distributor enquiry button or WhatsApp contact option and the team can guide you.'],
        ['q' => 'Is there a catalogue available?', 'a' => 'You can use the catalogue link from the website footer or contact the team for updated product information.'],
    ];
}
public_header($settings, $seoTitle, $seoDescription, 'products');
?>
<?= json_ld(breadcrumb_schema([['name'=>'Home','url'=>home_url()],['name'=>'Products','url'=>home_url('products')],['name'=>$product['name_en'] ?? 'Product','url'=>product_url($product)]])) ?>
<?= json_ld(product_schema($product, $settings)) ?>
<?php if($faqs): ?><?= json_ld(product_faq_schema($faqs, $product)) ?><?php endif; ?>
<main id="main-content" class="inner-page-main product-detail-page-v32 product-detail-page-v40">
  <section class="product-hero-v32 section-pad" style="--accent:<?= e($product['accent_color'] ?? '#f5a623') ?>">
    <div class="container product-hero-grid-v32">
      <div class="product-gallery-v32 reveal">
        <div class="product-main-shot-v32">
          <span class="product-glow-v32"></span>
          <img src="<?= e(optimized_image_url($product['image'])) ?>" alt="<?= e($product['name_en']) ?> mosquito coil pack" loading="eager" fetchpriority="high" decoding="async"<?= image_dimension_attributes($product['image']) ?>>
        </div>
        <?php if(count($gallery) > 1): ?>
        <div class="product-thumbs-v32" aria-label="Product gallery">
          <?php foreach($gallery as $img): ?>
            <a href="<?= e(optimized_image_url($img)) ?>" data-lightbox-gallery="product-gallery" data-lightbox-title="<?= e($product['name_en']) ?>"><img src="<?= e(optimized_image_url($img)) ?>" alt="<?= e($product['name_en']) ?> gallery image" loading="lazy"></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <article class="product-copy-v32 reveal">
        <span class="eyebrow"><?= e($product['brand']) ?> · <?= e($product['badge'] ?? 'Mosquito Coil') ?></span>
        <h1 data-en="<?= e($product['name_en']) ?>" data-bn="<?= e($product['name_bn']) ?>"><?= e($product['name_en']) ?></h1>
        <p data-en="<?= e($product['short_description_en'] ?? '') ?>" data-bn="<?= e($product['short_description_bn'] ?? '') ?>"><?= e($product['short_description_en'] ?? '') ?></p>
        <div class="product-kpi-row-v32">
          <span><b><?= e($product['protection_hours'] ?? '—') ?></b><em data-en="Protection" data-bn="সুরক্ষা">Protection</em></span>
          <span><b><?= e($product['brand']) ?></b><em data-en="Brand" data-bn="ব্র্যান্ড">Brand</em></span>
          <span><b><?= e($product['category'] ?? 'Coil') ?></b><em data-en="Category" data-bn="ক্যাটাগরি">Category</em></span>
        </div>
        <div class="detail-features-v15 product-tags-v32"><?php foreach($features as $f): ?><span><?= e($f) ?></span><?php endforeach; ?></div>
        <div class="detail-actions-v15 product-actions-v32">
          <a class="btn btn-green" href="<?= e(whatsapp_url($settings['whatsapp_number'] ?? '', 'Hello, I want to know more about ' . ($product['name_en'] ?? 'this product') . '.')) ?>" target="_blank" rel="noopener" data-en="WhatsApp Enquiry" data-bn="হোয়াটসঅ্যাপ এনকোয়ারি">WhatsApp Enquiry</a>
          <a class="btn btn-red" href="#productEnquiryForm" data-en="Product Enquiry" data-bn="পণ্য ইনকোয়ারি">Product Enquiry</a>
          <a class="btn btn-ghost" href="<?= e($catalogueUrl) ?>" data-en="Download Catalogue" data-bn="ক্যাটালগ ডাউনলোড">Download Catalogue</a>
          <a class="btn btn-ghost" href="<?= e(home_url('products')) ?>" data-en="Back to Products" data-bn="পণ্যে ফিরে যান">Back to Products</a>
        </div>
      </article>
    </div>
  </section>

  <section class="section-pad-sm product-spec-section-v32">
    <div class="container product-spec-grid-v32">
      <article class="product-panel-v32 reveal">
        <span class="eyebrow" data-en="Product Specifications" data-bn="পণ্যের স্পেসিফিকেশন">Product Specifications</span>
        <h2 data-en="Trade-ready product information" data-bn="ট্রেডের জন্য প্রস্তুত পণ্য তথ্য">Trade-ready product information</h2>
        <div class="spec-table-v32">
          <?php foreach($specs as $row): ?>
            <div><span data-en="<?= e($row['label_en']) ?>" data-bn="<?= e($row['label_bn']) ?>"><?= e($row['label_en']) ?></span><b><?= e($row['value']) ?></b></div>
          <?php endforeach; ?>
        </div>
      </article>
      <article class="product-panel-v32 product-use-v32 reveal">
        <span class="eyebrow" data-en="Usage & Safety" data-bn="ব্যবহার ও নিরাপত্তা">Usage & Safety</span>
        <h2 data-en="Use responsibly for better comfort" data-bn="ভালো আরামের জন্য দায়িত্বশীল ব্যবহার">Use responsibly for better comfort</h2>
        <div class="use-card-v32"><b data-en="How to use" data-bn="ব্যবহার পদ্ধতি">How to use</b><p data-en="<?= e($usageEn) ?>" data-bn="<?= e($usageBn) ?>"><?= e($usageEn) ?></p></div>
        <div class="use-card-v32 warning"><b data-en="Safety note" data-bn="নিরাপত্তা নোট">Safety note</b><p data-en="<?= e($safetyEn) ?>" data-bn="<?= e($safetyBn) ?>"><?= e($safetyEn) ?></p></div>
      </article>
    </div>
  </section>

  <section class="section-pad-sm product-faq-section-v32">
    <div class="container product-two-col-v32">
      <div class="section-head compact-head reveal">
        <span class="eyebrow" data-en="Product FAQ" data-bn="পণ্য সম্পর্কিত প্রশ্ন">Product FAQ</span>
        <h2 data-en="Clear answers for customers and distributors" data-bn="কাস্টমার ও ডিস্ট্রিবিউটরের জন্য পরিষ্কার উত্তর">Clear answers for customers and distributors</h2>
      </div>
      <div class="faq-list-v32 reveal">
        <?php foreach($faqs as $item): ?>
          <details>
            <summary><?= e($item['q'] ?? '') ?></summary>
            <p><?= e($item['a'] ?? '') ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section-pad-sm product-comparison-section-v40">
    <div class="container">
      <div class="section-head compact-head reveal">
        <span class="eyebrow" data-en="Product Comparison" data-bn="পণ্য তুলনা">Product Comparison</span>
        <h2 data-en="Choose the right product for your market" data-bn="আপনার বাজারের জন্য সঠিক পণ্য বেছে নিন">Choose the right product for your market</h2>
      </div>
      <div class="comparison-table-v40 reveal" role="table" aria-label="Product comparison">
        <div class="comparison-row-v40 comparison-head-v40" role="row"><span>Product</span><span>Protection</span><span>Pack</span><span>Smoke / Comfort</span><span>Best For</span><span>Highlight</span></div>
        <?php foreach($comparisonRows as $row): ?>
          <a class="comparison-row-v40" role="row" href="<?= e(product_url($row)) ?>" style="--accent:<?= e($row['accent_color']) ?>">
            <span><b data-en="<?= e($row['name_en']) ?>" data-bn="<?= e($row['name_bn']) ?>"><?= e($row['name_en']) ?></b><em><?= e($row['brand']) ?></em></span>
            <span><?= e($row['protection']) ?></span>
            <span><?= e($row['pack_size']) ?></span>
            <span><?= e($row['smoke_type']) ?></span>
            <span><?= e($row['best_for']) ?></span>
            <span><?= e($row['highlight']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section-pad-sm product-lead-section-v40">
    <div class="container product-lead-grid-v40">
      <article class="product-catalogue-card-v40 reveal">
        <span class="eyebrow" data-en="Catalogue System" data-bn="ক্যাটালগ সিস্টেম">Catalogue System</span>
        <h2 data-en="<?= e(cms_setting($settings,'catalogue_title_en')) ?>" data-bn="<?= e(cms_setting($settings,'catalogue_title_bn')) ?>"><?= e(cms_setting($settings,'catalogue_title_en')) ?></h2>
        <p data-en="<?= e(cms_setting($settings,'catalogue_note_en')) ?>" data-bn="<?= e(cms_setting($settings,'catalogue_note_bn')) ?>"><?= e(cms_setting($settings,'catalogue_note_en')) ?></p>
        <div class="catalogue-meta-v40">
          <span><b data-en="Version" data-bn="ভার্সন">Version</b><?= e(cms_setting($settings,'catalogue_version')) ?></span>
          <span><b data-en="Updated" data-bn="আপডেট">Updated</b><?= e(cms_setting($settings,'catalogue_updated_at')) ?></span>
        </div>
        <a class="btn btn-green" href="<?= e($catalogueUrl) ?>" data-catalogue-download data-product-name="<?= e($product['name_en']) ?>" data-en="Download Product Catalogue" data-bn="পণ্য ক্যাটালগ ডাউনলোড">Download Product Catalogue</a>
      </article>

      <form class="distributor-form lead-enquiry-form product-enquiry-form-v40 reveal" id="productEnquiryForm" method="post" action="api/enquiry.php">
        <?= csrf_field() ?>
        <h3 data-en="Ask about <?= e($product['name_en']) ?>" data-bn="<?= e($product['name_bn']) ?> সম্পর্কে জানতে চান">Ask about <?= e($product['name_en']) ?></h3>
        <input type="hidden" name="lead_source" value="Product Page">
        <input type="hidden" name="enquiry_type" value="Product Enquiry">
        <input type="hidden" name="product_slug" value="<?= e($product['slug']) ?>">
        <input type="hidden" name="product_name" value="<?= e($product['name_en']) ?>">
        <input type="hidden" name="interested_brand" value="<?= e($product['brand']) ?>">
        <label class="lead-hp-field" aria-hidden="true" tabindex="-1"><span>Leave this field blank</span><input name="website" tabindex="-1" autocomplete="off"></label>
        <label><span data-en="Your Name" data-bn="আপনার নাম">Your Name</span><input name="name" required maxlength="120" autocomplete="name"></label>
        <label><span data-en="Phone Number" data-bn="ফোন নম্বর">Phone Number</span><input name="phone" required maxlength="30" autocomplete="tel" inputmode="tel"></label>
        <label><span data-en="Shop / Company" data-bn="দোকান / কোম্পানি">Shop / Company</span><input name="company_name" maxlength="160" autocomplete="organization"></label>
        <label><span data-en="District" data-bn="জেলা">District</span><select name="district" required><option value="">Select district</option><?php foreach($districts as $d): ?><option value="<?= e($d) ?>"><?= e($d) ?></option><?php endforeach; ?></select></label>
        <label><span data-en="Business Type" data-bn="ব্যবসার ধরন">Business Type</span><select name="business_type" required><option>Retailer</option><option>Wholesaler</option><option>Distributor</option><option>Super Shop</option><option>Other</option></select></label>
        <label class="full"><span data-en="Business Address / Area" data-bn="ব্যবসার ঠিকানা / এলাকা">Business Address / Area</span><input name="business_address" maxlength="220"></label>
        <label class="full"><span data-en="Message" data-bn="মেসেজ">Message</span><textarea name="message" rows="3" maxlength="800">I want to know more about <?= e($product['name_en']) ?>.</textarea></label>
        <button class="btn btn-green" type="submit" data-en="Submit Product Enquiry" data-bn="পণ্য ইনকোয়ারি সাবমিট করুন">Submit Product Enquiry</button>
        <p class="form-status" role="status"></p>
        <div class="form-success-actions-v39" hidden>
          <a class="btn btn-red form-whatsapp-link" href="#" target="_blank" rel="noopener" data-en="Continue on WhatsApp" data-bn="WhatsApp-এ কথা বলুন">Continue on WhatsApp</a>
          <small data-en="Your product enquiry is saved. WhatsApp is optional for faster response." data-bn="আপনার পণ্য ইনকোয়ারি জমা হয়েছে। দ্রুত রেসপন্সের জন্য WhatsApp করতে পারেন।">Your product enquiry is saved. WhatsApp is optional for faster response.</small>
        </div>
      </form>
    </div>
  </section>

  <?php if($related): ?>
  <section class="section-pad-sm related-products-v32">
    <div class="container">
      <div class="section-head compact-head reveal">
        <span class="eyebrow" data-en="Related Products" data-bn="সম্পর্কিত পণ্য">Related Products</span>
        <h2 data-en="Compare with other portfolio items" data-bn="অন্যান্য পণ্যের সাথে তুলনা করুন">Compare with other portfolio items</h2>
      </div>
      <div class="related-grid-v32">
        <?php foreach($related as $rp): $rf = json_features($rp['features']); ?>
          <a class="related-card-v32 reveal" href="<?= e(product_url($rp)) ?>" style="--accent:<?= e($rp['accent_color'] ?? '#f5a623') ?>">
            <img src="<?= e(optimized_image_url($rp['image'])) ?>" alt="<?= e($rp['name_en']) ?>" loading="lazy">
            <span><?= e($rp['brand']) ?> · <?= e($rp['protection_hours'] ?? '') ?></span>
            <b data-en="<?= e($rp['name_en']) ?>" data-bn="<?= e($rp['name_bn']) ?>"><?= e($rp['name_en']) ?></b>
            <em><?= e($rf[0] ?? 'View details') ?> →</em>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
</main>
<?php public_footer($settings, $products); ?>

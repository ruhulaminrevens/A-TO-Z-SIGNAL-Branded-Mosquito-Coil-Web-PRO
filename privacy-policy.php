<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/site-ui.php';
$settings = get_site_settings();
$products = get_products(true);
public_header($settings, 'Privacy Policy', 'Privacy policy for A TO Z and SIGNAL mosquito coil website and distributor enquiry data handling.', '');
?>
<main id="main-content" class="inner-page-main">
  <section class="page-hero section-pad">
    <div class="container">
      <span class="eyebrow">Privacy Policy</span>
      <h1>Privacy Policy</h1>
      <p>Your privacy matters to us. This page explains how enquiry and contact information is handled.</p>
    </div>
  </section>
  <section class="section-pad-sm">
    <div class="container policy-card">
      <h2>Information We Collect</h2>
      <p>When you submit an enquiry, we may collect your name, phone number, district, shop or company name, business type, interested brand, product interest and message so our team can respond properly.</p>
      <h2>How We Use Information</h2>
      <p>Information is used for product enquiries, dealer support, distributor communication, catalogue requests and business follow-up.</p>
      <h2>Data Sharing</h2>
      <p>We do not sell personal enquiry information. Details may be shared internally only for business support and response purposes.</p>
      <h2>Contact</h2>
      <p>For privacy-related questions, contact us at <?= e($settings['email']) ?>.</p>
    </div>
  </section>
</main>
<?php public_footer($settings, $products); ?>

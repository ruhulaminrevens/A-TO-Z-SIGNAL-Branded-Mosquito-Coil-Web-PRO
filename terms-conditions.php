<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/site-ui.php';
$settings = get_site_settings();
$products = get_products(true);
public_header($settings, 'Terms & Conditions', 'Terms and conditions for A TO Z and SIGNAL mosquito coil website, product information and enquiry services.', '');
?>
<main id="main-content" class="inner-page-main">
  <section class="page-hero section-pad">
    <div class="container">
      <span class="eyebrow">Terms & Conditions</span>
      <h1>Terms & Conditions</h1>
      <p>These terms guide the use of this website, product information and enquiry services.</p>
    </div>
  </section>
  <section class="section-pad-sm">
    <div class="container policy-card">
      <h2>Website Information</h2>
      <p>Product details, visuals and information are provided for general business and customer communication. Availability, packaging and specifications may vary by market and production update.</p>
      <h2>Enquiries</h2>
      <p>Submitting an enquiry does not automatically confirm dealership, distribution appointment or product availability. Our team will review and respond through official communication channels.</p>
      <h2>Brand Materials</h2>
      <p>All brand names, product visuals and website materials belong to their respective owners and should not be copied or reused without permission.</p>
      <h2>Contact</h2>
      <p>For official communication, use <?= e($settings['contact_phone']) ?> or <?= e($settings['email']) ?>.</p>
    </div>
  </section>
</main>
<?php public_footer($settings, $products); ?>

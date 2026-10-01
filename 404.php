<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/site-ui.php';
http_response_code(404);
$settings = get_site_settings();
$products = get_products(true);
public_header($settings, 'Page Not Found', 'The requested page could not be found on A TO Z & SIGNAL official website.', '');
?>
<main id="main-content" class="error-page-v39 section-pad">
    <div class="container error-card-v39 reveal">
        <span class="eyebrow" data-en="404 Error" data-bn="৪০৪ এরর">404 Error</span>
        <h1 data-en="This page could not be found" data-bn="এই পেজটি খুঁজে পাওয়া যায়নি">This page could not be found</h1>
        <p data-en="The link may be old, moved or typed incorrectly. You can return to the homepage, explore products or contact our team directly." data-bn="লিংকটি পুরোনো, সরানো হয়েছে বা ভুল টাইপ করা হয়েছে। হোমপেজে ফিরে যান, পণ্য দেখুন অথবা সরাসরি আমাদের টিমের সাথে যোগাযোগ করুন।">The link may be old, moved or typed incorrectly. You can return to the homepage, explore products or contact our team directly.</p>
        <div class="hero-actions">
            <a class="btn btn-red" href="<?= e(home_url()) ?>" data-en="Back to Homepage" data-bn="হোমপেজে ফিরুন">Back to Homepage</a>
            <a class="btn btn-green" href="<?= e(home_url('distributorForm')) ?>" data-en="Distributor Enquiry" data-bn="ডিস্ট্রিবিউটর ইনকোয়ারি">Distributor Enquiry</a>
            <a class="btn btn-ghost" href="<?= e(page_url('blogs')) ?>" data-en="Blog / Archive" data-bn="ব্লগ / আর্কাইভ">Blog / Archive</a>
        </div>
        <?php if($products): ?>
        <div class="error-suggestions-v42" aria-label="Popular product links">
            <b>Popular products</b>
            <?php foreach(array_slice($products, 0, 4) as $p): ?>
                <a href="<?= e(product_url($p)) ?>"><?= e($p['name_en']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php public_footer($settings, $products); ?>

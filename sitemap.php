<?php
require_once __DIR__ . '/includes/db.php';
ensure_phase5_marketing_schema();
$products = get_products(true);
$posts = get_blog_posts(true);
$today = date('Y-m-d');
$fileDate = static function(string $file) use ($today): string {
    $mtime = @filemtime(__DIR__ . '/' . $file);
    return $mtime ? date('Y-m-d', $mtime) : $today;
};
$urls = [
    ['loc' => base_url(), 'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => $fileDate('index.php')],
    ['loc' => page_url('about'), 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $fileDate('about-us.php')],
    ['loc' => page_url('blogs'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $fileDate('blog.php')],
    ['loc' => page_url('privacy'), 'priority' => '0.4', 'changefreq' => 'yearly', 'lastmod' => $fileDate('privacy-policy.php')],
    ['loc' => page_url('terms'), 'priority' => '0.4', 'changefreq' => 'yearly', 'lastmod' => $fileDate('terms-conditions.php')],
];
foreach ($products as $p) {
    $urls[] = [
        'loc' => product_url($p),
        'priority' => '0.85',
        'changefreq' => 'weekly',
        'lastmod' => !empty($p['updated_at']) ? date('Y-m-d', strtotime((string)$p['updated_at'])) : $today,
    ];
}
foreach ($posts as $post) {
    $urls[] = [
        'loc' => blog_post_url($post),
        'priority' => !empty($post['is_featured']) ? '0.76' : '0.70',
        'changefreq' => 'monthly',
        'lastmod' => !empty($post['updated_at']) ? date('Y-m-d', strtotime((string)$post['updated_at'])) : (!empty($post['published_at']) ? date('Y-m-d', strtotime((string)$post['published_at'])) : $today),
    ];
}
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach($urls as $url): ?>
  <url>
    <loc><?= e($url['loc']) ?></loc>
    <lastmod><?= e($url['lastmod']) ?></lastmod>
    <changefreq><?= e($url['changefreq']) ?></changefreq>
    <priority><?= e($url['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>

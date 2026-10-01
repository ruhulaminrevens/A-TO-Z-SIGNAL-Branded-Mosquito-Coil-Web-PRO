<?php
require_once __DIR__ . '/includes/db.php';
$lines = [
    'User-agent: *',
    'Allow: /',
    'Disallow: /admin/',
    'Disallow: /config/',
    'Disallow: /includes/',
    'Disallow: /database/',
    'Disallow: /uploads/admin/',
    'Disallow: /*?*utm_',
    'Disallow: /*?*fbclid=',
    'Disallow: /*?*gclid=',
    '',
    'Sitemap: ' . page_url('sitemap'),
];
header('Content-Type: text/plain; charset=utf-8');
echo implode("\n", $lines) . "\n";

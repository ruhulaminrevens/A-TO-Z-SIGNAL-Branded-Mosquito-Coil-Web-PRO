<?php
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_cache_limiter('');
    }
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if (!headers_sent()) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    session_start();
}

function app_config(): array {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }
    return $config;
}

function app_asset_version(): string {
    $config = app_config();
    return (string)($config['asset_version'] ?? '1.0.0');
}

function app_package_version(): string {
    $config = app_config();
    return (string)($config['app_version'] ?? 'A TO Z & SIGNAL PHP');
}

function base_url(string $path = ''): string {
    $config = app_config();
    $base = trim((string)($config['base_url'] ?? ''));
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $script = rtrim($script, '/');
        if (str_ends_with($script, '/admin') || str_ends_with($script, '/api')) {
            $script = dirname($script);
            $script = $script === DIRECTORY_SEPARATOR ? '' : $script;
        }
        $base = $scheme . '://' . $host . ($script === '/' ? '' : $script);
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid security token. Please refresh and try again.');
    }
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text ?: 'product-' . time();
}

function json_features($features): array {
    if (is_array($features)) return $features;
    $decoded = json_decode((string)$features, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
    $lines = preg_split('/\r\n|\r|\n|,/', (string)$features);
    return array_values(array_filter(array_map('trim', $lines)));
}

function default_products(): array {
    return [
        [
            'id' => 1, 'brand' => 'A TO Z', 'name_en' => 'A TO Z Jumbo Coil', 'name_bn' => 'এ টু জেড জাম্বো কয়েল',
            'slug' => 'atoz-jumbo-coil', 'category' => 'Mosquito Coil', 'protection_hours' => '10H', 'badge' => 'Jumbo Size',
            'short_description_en' => 'Jumbo-size protection with steady burning for bigger rooms.',
            'short_description_bn' => 'বড় ঘরের জন্য স্থিতিশীল বার্নিংসহ জাম্বো সাইজ সুরক্ষা।',
            'features' => '["10-hour protection","Large area coverage","Strong formula","Reliable burning"]',
            'image' => 'uploads/products/atoz-jumbo.webp', 'accent_color' => '#f5a623', 'sort_order' => 1, 'is_active' => 1
        ],
        [
            'id' => 2, 'brand' => 'A TO Z', 'name_en' => 'A TO Z Super Power Coil', 'name_bn' => 'এ টু জেড সুপার পাওয়ার কয়েল',
            'slug' => 'atoz-super-power-coil', 'category' => 'Mosquito Coil', 'protection_hours' => '12H', 'badge' => 'Best Seller',
            'short_description_en' => 'Strong daily mosquito defense with dependable overnight performance.',
            'short_description_bn' => 'সারারাত নির্ভরযোগ্য পারফরম্যান্সসহ শক্তিশালী দৈনন্দিন মশা প্রতিরোধ।',
            'features' => '["12-hour protection","Strong knockdown","Mass killing formula","Premium quality"]',
            'image' => 'uploads/products/atoz-super-power.webp', 'accent_color' => '#ffc533', 'sort_order' => 2, 'is_active' => 1
        ],
        [
            'id' => 3, 'brand' => 'A TO Z', 'name_en' => 'A TO Z Turbo Coil', 'name_bn' => 'এ টু জেড টার্বো কয়েল',
            'slug' => 'atoz-turbo-coil', 'category' => 'Mosquito Coil', 'protection_hours' => '12H', 'badge' => 'Turbo Action',
            'short_description_en' => 'Fast-action coil experience with bold protection and ember glow.',
            'short_description_bn' => 'দ্রুত অ্যাকশন, শক্তিশালী সুরক্ষা এবং এম্বার গ্লো সহ টার্বো কয়েল।',
            'features' => '["Turbo action","Rapid mosquito defense","Premium burn","Bold fragrance"]',
            'image' => 'uploads/products/atoz-turbo.webp', 'accent_color' => '#ff6b35', 'sort_order' => 3, 'is_active' => 1
        ],
        [
            'id' => 4, 'brand' => 'SIGNAL', 'name_en' => 'SIGNAL Plus Coil', 'name_bn' => 'সিগনাল প্লাস কয়েল',
            'slug' => 'signal-plus-coil', 'category' => 'Mosquito Coil', 'protection_hours' => '12H', 'badge' => 'Plus Formula',
            'short_description_en' => 'Balanced premium formula with strong protection and low-smoke comfort.',
            'short_description_bn' => 'শক্তিশালী সুরক্ষা ও কম ধোঁয়ার আরামের ব্যালান্সড প্রিমিয়াম ফর্মুলা।',
            'features' => '["Low smoke tech","Pleasant scent","Family comfort","Advanced formula"]',
            'image' => 'uploads/products/signal-plus.webp', 'accent_color' => '#2bbf5a', 'sort_order' => 4, 'is_active' => 1
        ],
        [
            'id' => 5, 'brand' => 'SIGNAL', 'name_en' => 'SIGNAL Jama Coil', 'name_bn' => 'সিগনাল জামা কয়েল',
            'slug' => 'signal-jama-coil', 'category' => 'Mosquito Coil', 'protection_hours' => '12H', 'badge' => 'Daily Value',
            'short_description_en' => 'Everyday protection made for retailer-friendly demand and family trust.',
            'short_description_bn' => 'রিটেইলার-ফ্রেন্ডলি ডিমান্ড ও পরিবারিক বিশ্বাসের জন্য দৈনন্দিন সুরক্ষা।',
            'features' => '["Daily protection","Stable burning","Retailer friendly","Trusted quality"]',
            'image' => 'uploads/products/signal-jama.webp', 'accent_color' => '#34d66b', 'sort_order' => 5, 'is_active' => 1
        ],
        [
            'id' => 6, 'brand' => 'SIGNAL', 'name_en' => 'SIGNAL Mega Coil', 'name_bn' => 'সিগনাল মেগা কয়েল',
            'slug' => 'signal-mega-coil', 'category' => 'Mosquito Coil', 'protection_hours' => '12H', 'badge' => 'Mega Shield',
            'short_description_en' => 'Wider protection shield with premium packaging and confident brand appeal.',
            'short_description_bn' => 'প্রিমিয়াম প্যাকেজিং ও শক্তিশালী ব্র্যান্ড অ্যাপিলসহ ওয়াইড প্রোটেকশন শিল্ড।',
            'features' => '["Wide shield","Premium pack","Long lasting","Home protection"]',
            'image' => 'uploads/products/signal-mega.webp', 'accent_color' => '#41e071', 'sort_order' => 6, 'is_active' => 1
        ],
        [
            'id' => 7, 'brand' => 'SIGNAL', 'name_en' => 'SIGNAL Spider Micro Smoke', 'name_bn' => 'সিগনাল স্পাইডার মাইক্রো স্মোক',
            'slug' => 'signal-spider-micro-smoke', 'category' => 'Mosquito Coil', 'protection_hours' => '10H', 'badge' => 'Micro Smoke',
            'short_description_en' => 'Modern micro-smoke concept with powerful coverage and cleaner visuals.',
            'short_description_bn' => 'শক্তিশালী কাভারেজ ও পরিষ্কার ভিজ্যুয়ালের জন্য মডার্ন মাইক্রো-স্মোক কনসেপ্ট।',
            'features' => '["Micro smoke tech","Deep corner reach","Modern pack","Powerful defense"]',
            'image' => 'uploads/products/signal-spider.webp', 'accent_color' => '#ff4040', 'sort_order' => 7, 'is_active' => 1
        ],
    ];
}

function default_settings(): array {
    $config = app_config();
    $base = [
        'site_name' => $config['site_name'] ?? 'A TO Z & SIGNAL',
        'contact_phone' => $config['contact_phone'] ?? '',
        'whatsapp_number' => $config['whatsapp_number'] ?? '',
        'email' => $config['email'] ?? '',
        'facebook' => $config['facebook'] ?? '',
        'office_address_en' => $config['office_address_en'] ?? '',
        'office_address_bn' => $config['office_address_bn'] ?? '',
        'catalogue_url' => $config['catalogue_url'] ?? '#',
        'catalogue_title_en' => $config['catalogue_title_en'] ?? 'A TO Z & SIGNAL Product Catalogue',
        'catalogue_title_bn' => $config['catalogue_title_bn'] ?? 'এ টু জেড ও সিগনাল পণ্য ক্যাটালগ',
        'catalogue_version' => $config['catalogue_version'] ?? 'Trade Edition',
        'catalogue_updated_at' => $config['catalogue_updated_at'] ?? '',
        'catalogue_note_en' => $config['catalogue_note_en'] ?? 'Download the latest product catalogue for trade and distribution reference.',
        'catalogue_note_bn' => $config['catalogue_note_bn'] ?? 'ট্রেড ও ডিস্ট্রিবিউশন রেফারেন্সের জন্য সর্বশেষ পণ্য ক্যাটালগ ডাউনলোড করুন.',
    ];
    if (function_exists('phase2_setting_defaults')) {
        $base = array_merge(phase2_setting_defaults(), $base);
    }
    if (function_exists('phase5_setting_defaults')) {
        $base = array_merge(phase5_setting_defaults(), $base);
    }
    return $base;
}

function district_list(): array {
    return ['Bagerhat','Bandarban','Barguna','Barishal','Bhola','Bogura','Brahmanbaria','Chandpur','Chapai Nawabganj','Chattogram','Chuadanga','Cumilla','Cox’s Bazar','Dhaka','Dinajpur','Faridpur','Feni','Gaibandha','Gazipur','Gopalganj','Habiganj','Jamalpur','Jashore','Jhalokathi','Jhenaidah','Joypurhat','Khagrachhari','Khulna','Kishoreganj','Kurigram','Kushtia','Lakshmipur','Lalmonirhat','Madaripur','Magura','Manikganj','Meherpur','Moulvibazar','Munshiganj','Mymensingh','Naogaon','Narail','Narayanganj','Narsingdi','Natore','Netrokona','Nilphamari','Noakhali','Pabna','Panchagarh','Patuakhali','Pirojpur','Rajbari','Rajshahi','Rangamati','Rangpur','Satkhira','Shariatpur','Sherpur','Sirajganj','Sunamganj','Sylhet','Tangail','Thakurgaon'];
}

function get_products(bool $activeOnly = true): array {
    if (function_exists('ensure_phase3_product_schema')) ensure_phase3_product_schema();
    $pdo = try_db();
    if (!$pdo) return default_products();
    $sql = 'SELECT * FROM products ' . ($activeOnly ? 'WHERE is_active = 1 ' : '') . 'ORDER BY sort_order ASC, id DESC';
    try {
        return $pdo->query($sql)->fetchAll() ?: default_products();
    } catch (Throwable $e) {
        return default_products();
    }
}

function get_product_by_slug(string $slug): ?array {
    if (function_exists('ensure_phase3_product_schema')) ensure_phase3_product_schema();
    $pdo = try_db();
    if ($pdo) {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE slug = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if ($row) return $row;
    }
    foreach (default_products() as $p) if ($p['slug'] === $slug) return $p;
    return null;
}

function get_site_settings(): array {
    $settings = default_settings();
    $pdo = try_db();
    if (!$pdo) return $settings;
    try {
        $rows = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Throwable $e) {}
    return $settings;
}


function ensure_blog_table(): void {
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS blog_posts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title_en VARCHAR(220) NOT NULL,
            title_bn VARCHAR(220) DEFAULT NULL,
            slug VARCHAR(240) NOT NULL UNIQUE,
            excerpt_en TEXT NULL,
            excerpt_bn TEXT NULL,
            content_en MEDIUMTEXT NULL,
            content_bn MEDIUMTEXT NULL,
            image VARCHAR(255) DEFAULT NULL,
            published_at DATE DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_blog_publish (is_published, published_at, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {}
}

function default_blog_posts(): array {
    return [
        ['id'=>1,'title_en'=>'1st Sales Meet 2017','title_bn'=>'১ম সেলস মিট ২০১৭','slug'=>'sales-meet-2017','excerpt_en'=>'A milestone gathering that strengthened the early distribution journey of Nabiad Distribution Limited.','excerpt_bn'=>'নাবীয়াদ ডিস্ট্রিবিউশন লিমিটেডের প্রাথমিক ডিস্ট্রিবিউশন যাত্রাকে আরও শক্তিশালী করা একটি গুরুত্বপূর্ণ সেলস মিট।','content_en'=>'The 1st Sales Meet 2017 marked an important step in building a stronger sales culture, product confidence and distribution teamwork.','content_bn'=>'১ম সেলস মিট ২০১৭ সেলস কালচার, পণ্যের প্রতি আত্মবিশ্বাস এবং ডিস্ট্রিবিউশন টিমওয়ার্ককে আরও শক্তিশালী করার একটি গুরুত্বপূর্ণ ধাপ ছিল।','image'=>'uploads/blog/sales-meet-2017.jpeg','published_at'=>'2017-09-19','sort_order'=>1,'is_published'=>1],
        ['id'=>2,'title_en'=>'Launching Ceremony 2018','title_bn'=>'লঞ্চিং সিরেমনি ২০১৮','slug'=>'launching-ceremony-2018','excerpt_en'=>'Product launch event highlighting brand growth and channel development.','excerpt_bn'=>'ব্র্যান্ড গ্রোথ ও চ্যানেল ডেভেলপমেন্টকে কেন্দ্র করে আয়োজিত পণ্য লঞ্চিং ইভেন্ট।','content_en'=>'The 2018 launching ceremony brought together sales leaders and business partners for stronger product visibility and retail momentum.','content_bn'=>'২০১৮ সালের লঞ্চিং সিরেমনি সেলস লিডার ও বিজনেস পার্টনারদের একত্রিত করে পণ্যের দৃশ্যমানতা ও রিটেইল মোমেন্টামকে শক্তিশালী করেছে।','image'=>'uploads/blog/launching-ceremony-2018.jpeg','published_at'=>'2018-04-28','sort_order'=>2,'is_published'=>1],
        ['id'=>3,'title_en'=>'Annual Sales Meet 2021','title_bn'=>'বার্ষিক সেলস মিট ২০২১','slug'=>'annual-sales-meet-2021','excerpt_en'=>'Annual sales meet focused on alignment, team spirit and market execution.','excerpt_bn'=>'সমন্বয়, টিম স্পিরিট এবং বাজার বাস্তবায়নকে কেন্দ্র করে বার্ষিক সেলস মিট।','content_en'=>'Annual Sales Meet 2021 focused on distribution discipline, sales planning and stronger market execution across key territories.','content_bn'=>'বার্ষিক সেলস মিট ২০২১ মূল টেরিটরিগুলোতে ডিস্ট্রিবিউশন শৃঙ্খলা, সেলস প্ল্যানিং এবং বাজার বাস্তবায়নকে শক্তিশালী করার উপর গুরুত্ব দিয়েছে।','image'=>'uploads/blog/annual-sales-meet-2021.jpeg','published_at'=>'2021-02-06','sort_order'=>3,'is_published'=>1],
        ['id'=>4,'title_en'=>'SIGNAL Spider Launch 2024','title_bn'=>'সিগনাল স্পাইডার লঞ্চ ২০২৪','slug'=>'signal-spider-launch-2024','excerpt_en'=>'A premium launch moment for SIGNAL Spider Micro Smoke, introducing a modern product story.','excerpt_bn'=>'SIGNAL Spider Micro Smoke-এর আধুনিক পণ্য গল্পকে সামনে আনা একটি প্রিমিয়াম লঞ্চ মুহূর্ত।','content_en'=>'SIGNAL Spider Micro Smoke launch activities highlighted modern product appeal, channel confidence and a refreshed market presence.','content_bn'=>'SIGNAL Spider Micro Smoke লঞ্চ কার্যক্রম আধুনিক পণ্য উপস্থাপনা, চ্যানেল আস্থা এবং নতুন বাজার উপস্থিতিকে সামনে এনেছে।','image'=>'uploads/blog/sales-meet-2024-launch.jpg','published_at'=>'2024-01-01','sort_order'=>4,'is_published'=>1],
        ['id'=>5,'title_en'=>'6th Sales Meet 2025','title_bn'=>'৬ষ্ঠ সেলস মিট ২০২৫','slug'=>'sales-meet-2025','excerpt_en'=>'A major team event reflecting the distribution strength and future growth focus of Nabiad Distribution Limited.','excerpt_bn'=>'নাবীয়াদ ডিস্ট্রিবিউশন লিমিটেডের ডিস্ট্রিবিউশন শক্তি ও ভবিষ্যৎ গ্রোথ ফোকাস তুলে ধরা একটি গুরুত্বপূর্ণ টিম ইভেন্ট।','content_en'=>'The 6th Sales Meet 2025 represents a stronger national team spirit, channel development and continued commitment to market growth.','content_bn'=>'৬ষ্ঠ সেলস মিট ২০২৫ জাতীয় টিম স্পিরিট, চ্যানেল ডেভেলপমেন্ট এবং বাজার প্রবৃদ্ধির প্রতি ধারাবাহিক অঙ্গীকারকে তুলে ধরে।','image'=>'uploads/blog/sales-meet-2025-stage.jpg','published_at'=>'2025-01-01','sort_order'=>5,'is_published'=>1],
    ];
}

function get_blog_posts(bool $publishedOnly = true): array {
    ensure_phase4_blog_schema();
    $pdo = try_db();
    if (!$pdo) return default_blog_posts();
    try {
        $sql = 'SELECT * FROM blog_posts ' . ($publishedOnly ? 'WHERE is_published = 1 ' : '') . 'ORDER BY COALESCE(published_at, created_at) DESC, sort_order ASC, id DESC';
        $rows = $pdo->query($sql)->fetchAll();
        return $rows ?: default_blog_posts();
    } catch (Throwable $e) { return default_blog_posts(); }
}

function get_blog_post_by_slug(string $slug): ?array {
    ensure_phase4_blog_schema();
    $pdo = try_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE slug = ? AND is_published = 1 LIMIT 1');
            $stmt->execute([$slug]);
            $row = $stmt->fetch();
            if ($row) return $row;
        } catch (Throwable $e) {}
    }
    foreach (default_blog_posts() as $p) if ($p['slug'] === $slug) return $p;
    return null;
}
function ensure_enquiry_crm_columns(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $stmt = $pdo->query('SHOW COLUMNS FROM distributor_enquiries');
        $columns = array_map(static fn($row) => $row['Field'] ?? '', $stmt->fetchAll());
        if (!in_array('phone_normalized', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD phone_normalized VARCHAR(40) DEFAULT NULL AFTER phone");
        }
        if (!in_array('company_name', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD company_name VARCHAR(160) DEFAULT NULL AFTER phone_normalized");
        }
        if (!in_array('business_address', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD business_address VARCHAR(220) DEFAULT NULL AFTER district");
        }
        if (!in_array('interested_brand', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD interested_brand VARCHAR(40) NOT NULL DEFAULT 'Both' AFTER business_type");
        }
        if (!in_array('product_id', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD product_id INT UNSIGNED NULL AFTER interested_brand");
        }
        if (!in_array('product_slug', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD product_slug VARCHAR(220) DEFAULT NULL AFTER product_id");
        }
        if (!in_array('product_name', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD product_name VARCHAR(190) DEFAULT NULL AFTER product_slug");
        }
        if (!in_array('enquiry_type', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD enquiry_type VARCHAR(60) NOT NULL DEFAULT 'Distributor' AFTER product_name");
        }
        if (!in_array('status', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD status VARCHAR(40) NOT NULL DEFAULT 'new' AFTER enquiry_type");
        }
        if (!in_array('lead_source', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD lead_source VARCHAR(80) DEFAULT NULL AFTER message");
        }
        if (!in_array('admin_note', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD admin_note TEXT NULL AFTER lead_source");
        }
        if (!in_array('follow_up_at', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD follow_up_at DATE NULL AFTER admin_note");
        }
        if (!in_array('handled_at', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD handled_at DATETIME NULL AFTER follow_up_at");
        }
        if (!in_array('updated_at', $columns, true)) {
            $pdo->exec("ALTER TABLE distributor_enquiries ADD updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        }
        try { $pdo->exec('CREATE INDEX idx_enquiry_status ON distributor_enquiries (status)'); } catch (Throwable $e) {}
        try { $pdo->exec('CREATE INDEX idx_enquiry_followup ON distributor_enquiries (follow_up_at)'); } catch (Throwable $e) {}
        try { $pdo->exec('CREATE INDEX idx_enquiry_phone_normalized ON distributor_enquiries (phone_normalized)'); } catch (Throwable $e) {}
        try { $pdo->exec('CREATE INDEX idx_enquiry_brand ON distributor_enquiries (interested_brand)'); } catch (Throwable $e) {}
        try { $pdo->exec('CREATE INDEX idx_enquiry_source ON distributor_enquiries (lead_source)'); } catch (Throwable $e) {}
        try { $pdo->exec('CREATE INDEX idx_enquiry_product ON distributor_enquiries (product_slug)'); } catch (Throwable $e) {}
        try { $pdo->exec('CREATE INDEX idx_enquiry_type ON distributor_enquiries (enquiry_type)'); } catch (Throwable $e) {}
        ensure_enquiry_notes_schema();
    } catch (Throwable $e) {
        // Keep the site usable on restrictive shared hosting.
    }
}

function ensure_enquiry_notes_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS enquiry_notes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            enquiry_id INT UNSIGNED NOT NULL,
            note TEXT NOT NULL,
            status_from VARCHAR(40) DEFAULT NULL,
            status_to VARCHAR(40) DEFAULT NULL,
            follow_up_at DATE DEFAULT NULL,
            created_by INT UNSIGNED DEFAULT NULL,
            created_by_name VARCHAR(120) DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_enquiry_notes_lead (enquiry_id),
            INDEX idx_enquiry_notes_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        // Notes history is an admin enhancement. Keep CRM usable if CREATE TABLE is restricted.
    }
}

function enquiry_status_options(): array {
    return [
        'new' => 'New',
        'contacted' => 'Contacted',
        'interested' => 'Interested',
        'converted' => 'Converted',
        'not_interested' => 'Not Interested',
        'rejected' => 'Rejected',
    ];
}

function enquiry_status_label(?string $status): string {
    $options = enquiry_status_options();
    $key = strtolower(trim((string)$status));
    return $options[$key] ?? $options['new'];
}

function enquiry_brand_options(): array {
    return ['A TO Z', 'SIGNAL', 'Both'];
}

function sanitize_enquiry_brand(?string $brand): string {
    $brand = trim((string)$brand);
    return in_array($brand, enquiry_brand_options(), true) ? $brand : 'Both';
}

function lead_source_options(): array {
    return ['Website', 'Facebook', 'Google', 'WhatsApp', 'Referral', 'Direct Visit', 'Other'];
}

function sanitize_lead_source(?string $source): string {
    $source = trim((string)$source);
    if ($source === '') return 'Website';
    foreach (lead_source_options() as $option) {
        if (strcasecmp($source, $option) === 0) return $option;
    }
    return function_exists('mb_substr') ? mb_substr($source, 0, 80) : substr($source, 0, 80);
}

function enquiry_type_options(): array {
    return ['Distributor', 'Product Enquiry', 'Catalogue Request'];
}

function sanitize_enquiry_type(?string $type): string {
    $type = trim((string)$type);
    foreach (enquiry_type_options() as $option) {
        if (strcasecmp($type, $option) === 0) return $option;
    }
    return 'Distributor';
}

function lead_whatsapp_message(array $lead): string {
    $name = trim((string)($lead['name'] ?? ''));
    $brand = trim((string)($lead['interested_brand'] ?? 'Both')) ?: 'Both';
    $company = trim((string)($lead['company_name'] ?? ''));
    $prefix = $name !== '' ? 'Hello ' . $name : 'Hello';
    $type = trim((string)($lead['enquiry_type'] ?? 'Distributor')) ?: 'Distributor';
    $product = trim((string)($lead['product_name'] ?? ''));
    $line = $prefix . ', thanks for your A TO Z & SIGNAL ' . strtolower($type) . '.';
    $line .= ' We received your interest for ' . $brand . '.';
    if ($product !== '') $line .= ' Product: ' . $product . '.';
    if ($company !== '') $line .= ' Company/Shop: ' . $company . '.';
    return $line;
}

function enquiry_duplicate_count(PDO $pdo, string $normalizedPhone): int {
    if ($normalizedPhone === '') return 0;
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM distributor_enquiries WHERE phone_normalized = ?');
        $stmt->execute([$normalizedPhone]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

function enquiry_final_statuses(): array {
    return ['converted', 'not_interested', 'rejected'];
}

function enquiry_active_status_condition(string $column = 'status'): string {
    return "({$column} IS NULL OR {$column} = '' OR {$column} NOT IN ('converted','not_interested','rejected'))";
}

function enquiry_add_note(PDO $pdo, int $enquiryId, string $note, ?string $statusFrom = null, ?string $statusTo = null, ?string $followUpAt = null): void {
    ensure_enquiry_notes_schema();
    $note = trim($note);
    if ($enquiryId <= 0 || $note === '') return;
    try {
        $adminId = !empty($_SESSION['admin_user_id']) ? (int)$_SESSION['admin_user_id'] : null;
        $adminName = function_exists('current_admin_name') ? current_admin_name() : 'Admin';
        $stmt = $pdo->prepare('INSERT INTO enquiry_notes (enquiry_id, note, status_from, status_to, follow_up_at, created_by, created_by_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$enquiryId, $note, $statusFrom, $statusTo, $followUpAt ?: null, $adminId, $adminName]);
    } catch (Throwable $e) {
        // Do not block a lead update just because note history failed.
    }
}

function enquiry_notes_for_ids(PDO $pdo, array $ids, int $perLead = 5): array {
    $ids = array_values(array_filter(array_unique(array_map('intval', $ids)), static fn($id) => $id > 0));
    if (!$ids) return [];
    ensure_enquiry_notes_schema();
    try {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM enquiry_notes WHERE enquiry_id IN ({$placeholders}) ORDER BY enquiry_id ASC, created_at DESC, id DESC");
        $stmt->execute($ids);
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $leadId = (int)($row['enquiry_id'] ?? 0);
            if ($leadId <= 0) continue;
            if (!isset($grouped[$leadId])) $grouped[$leadId] = [];
            if (count($grouped[$leadId]) < $perLead) $grouped[$leadId][] = $row;
        }
        return $grouped;
    } catch (Throwable $e) { return []; }
}

function enquiry_group_counts(PDO $pdo, string $column, int $limit = 8): array {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) return [];
    try {
        $stmt = $pdo->query("SELECT COALESCE(NULLIF({$column}, ''), 'Unknown') AS label, COUNT(*) AS total FROM distributor_enquiries GROUP BY COALESCE(NULLIF({$column}, ''), 'Unknown') ORDER BY total DESC LIMIT " . max(1, $limit));
        return $stmt->fetchAll();
    } catch (Throwable $e) { return []; }
}

function enquiry_status_counts(PDO $pdo): array {
    $counts = array_fill_keys(array_keys(enquiry_status_options()), 0);
    try {
        $rows = $pdo->query("SELECT COALESCE(NULLIF(status, ''), 'new') AS status_key, COUNT(*) AS total FROM distributor_enquiries GROUP BY COALESCE(NULLIF(status, ''), 'new')")->fetchAll();
        foreach ($rows as $row) {
            $key = (string)($row['status_key'] ?? 'new');
            if (!array_key_exists($key, $counts)) $key = 'new';
            $counts[$key] += (int)($row['total'] ?? 0);
        }
    } catch (Throwable $e) {}
    return $counts;
}

function enquiry_pipeline_rows(PDO $pdo, int $perStatus = 5): array {
    $result = [];
    foreach (array_keys(enquiry_status_options()) as $status) {
        try {
            if ($status === 'new') {
                $stmt = $pdo->prepare("SELECT * FROM distributor_enquiries WHERE status = 'new' OR status IS NULL OR status = '' ORDER BY created_at DESC, id DESC LIMIT ?");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM distributor_enquiries WHERE status = ? ORDER BY COALESCE(follow_up_at, created_at) ASC, id DESC LIMIT ?");
            }
            $stmt->bindValue(1, $status === 'new' ? $perStatus : $status, $status === 'new' ? PDO::PARAM_INT : PDO::PARAM_STR);
            if ($status !== 'new') $stmt->bindValue(2, $perStatus, PDO::PARAM_INT);
            $stmt->execute();
            $result[$status] = $stmt->fetchAll();
        } catch (Throwable $e) { $result[$status] = []; }
    }
    return $result;
}

function enquiry_duplicate_phone_groups(PDO $pdo, int $limit = 12): array {
    try {
        $stmt = $pdo->query("SELECT phone_normalized, COUNT(*) AS total, MAX(created_at) AS last_seen, GROUP_CONCAT(DISTINCT district ORDER BY district SEPARATOR ', ') AS districts FROM distributor_enquiries WHERE phone_normalized IS NOT NULL AND phone_normalized <> '' GROUP BY phone_normalized HAVING COUNT(*) > 1 ORDER BY total DESC, last_seen DESC LIMIT " . max(1, $limit));
        return $stmt->fetchAll();
    } catch (Throwable $e) { return []; }
}

function normalize_bd_phone(?string $phone): string {
    $digits = preg_replace('/\D+/', '', (string)$phone);
    if ($digits === '') return '';
    if (strlen($digits) === 11 && str_starts_with($digits, '0')) return '88' . $digits;
    if (strlen($digits) === 10 && str_starts_with($digits, '1')) return '880' . $digits;
    return $digits;
}

function whatsapp_url(?string $phone, string $message = ''): string {
    $digits = normalize_bd_phone($phone);
    if ($digits === '') return '#';
    return 'https://wa.me/' . $digits . ($message !== '' ? '?text=' . rawurlencode($message) : '');
}

function tel_url(?string $phone): string {
    $digits = normalize_bd_phone($phone);
    if ($digits === '') return '#';
    return 'tel:+' . $digits;
}



function pretty_urls_enabled(): bool {
    $config = app_config();
    return (bool)($config['pretty_urls'] ?? true);
}

function app_public_url(string $path = ''): string {
    return base_url($path);
}

function asset_url(string $path): string {
    $path = trim($path);
    if ($path === '') return base_url();
    if (preg_match('~^https?://~i', $path) || str_starts_with($path, 'data:')) return $path;
    return base_url(ltrim($path, '/'));
}

function home_url(string $section = ''): string {
    $url = base_url();
    $section = trim($section, "# \t\n\r\0\x0B");
    return $section !== '' ? rtrim($url, '/') . '/#' . rawurlencode($section) : $url;
}

function page_url(string $page): string {
    $map = [
        'home' => '',
        'about' => pretty_urls_enabled() ? 'about' : 'about-us.php',
        'blogs' => pretty_urls_enabled() ? 'blogs' : 'blog.php',
        'blog' => pretty_urls_enabled() ? 'blogs' : 'blog.php',
        'privacy' => pretty_urls_enabled() ? 'privacy-policy' : 'privacy-policy.php',
        'terms' => pretty_urls_enabled() ? 'terms-conditions' : 'terms-conditions.php',
        'sitemap' => pretty_urls_enabled() ? 'sitemap.xml' : 'sitemap.php',
        'robots' => 'robots.txt',
    ];
    $path = $map[$page] ?? ltrim($page, '/');
    return base_url($path);
}

function product_url($product): string {
    $slug = is_array($product) ? (string)($product['slug'] ?? '') : (string)$product;
    $slug = trim($slug);
    if ($slug === '') return home_url('products');
    return pretty_urls_enabled()
        ? base_url('product/' . rawurlencode($slug))
        : base_url('product-details.php?slug=' . rawurlencode($slug));
}

function blog_post_url($post): string {
    $slug = is_array($post) ? (string)($post['slug'] ?? '') : (string)$post;
    $slug = trim($slug);
    if ($slug === '') return page_url('blogs');
    return pretty_urls_enabled()
        ? base_url('blog/' . rawurlencode($slug))
        : base_url('blog-details.php?slug=' . rawurlencode($slug));
}

function blog_archive_url(array $params = []): string {
    $base = page_url('blogs');
    $params = array_filter($params, static fn($v) => $v !== null && $v !== '');
    return $params ? $base . '?' . http_build_query($params) : $base;
}

function catalogue_route_url(array $params = []): string {
    $base = base_url(pretty_urls_enabled() ? 'catalogue' : 'catalogue-download.php');
    $params = array_filter($params, static fn($v) => $v !== null && $v !== '');
    return $params ? $base . '?' . http_build_query($params) : $base;
}

function current_canonical_url(): string {
    $script = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script === 'product-details.php' && !empty($_GET['slug'])) return product_url((string)$_GET['slug']);
    if ($script === 'blog-details.php' && !empty($_GET['slug'])) return blog_post_url((string)$_GET['slug']);
    if ($script === 'blog.php') return blog_archive_url();
    if ($script === 'about-us.php') return page_url('about');
    if ($script === 'privacy-policy.php') return page_url('privacy');
    if ($script === 'terms-conditions.php') return page_url('terms');
    if ($script === 'sitemap.php') return page_url('sitemap');
    if ($script === 'index.php' || $script === '') return base_url();

    $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
    return base_url(ltrim($requestPath, '/'));
}

function seo_title(string $title, array $settings): string {
    $title = trim(preg_replace('/\s+/u', ' ', strip_tags($title)) ?? '');
    $suffix = trim((string)cms_setting($settings, 'global_seo_title_suffix'));
    if ($title === '') $title = $settings['site_name'] ?? 'A TO Z & SIGNAL';
    if ($suffix !== '' && stripos($title, $suffix) === false) $title .= ' | ' . $suffix;
    return function_exists('mb_substr') ? mb_substr($title, 0, 65) : substr($title, 0, 65);
}

function seo_description(string $description): string {
    $description = trim(preg_replace('/\s+/u', ' ', strip_tags($description)) ?? '');
    return function_exists('mb_substr') ? mb_substr($description, 0, 160) : substr($description, 0, 160);
}

function image_dimensions(?string $path): array {
    $path = trim((string)$path);
    if ($path === '' || preg_match('~^(?:https?:)?//|^data:~i', $path)) return [];
    $relative = ltrim((string)(parse_url($path, PHP_URL_PATH) ?: $path), '/');
    $root = realpath(__DIR__ . '/..');
    $file = realpath(__DIR__ . '/../' . $relative);
    if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) return [];
    $size = @getimagesize($file);
    return $size ? ['width' => (int)$size[0], 'height' => (int)$size[1]] : [];
}

function image_dimension_attributes(?string $path): string {
    $dimensions = image_dimensions($path);
    if (!$dimensions) return '';
    return ' width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '"';
}

function optimized_image_url(?string $path): string {
    $path = trim((string)$path);
    if ($path === '') return asset_url('assets/img/hero-atoz-pack.png');
    if (preg_match('~^https?://~i', $path) || str_starts_with($path, 'data:')) return $path;

    $relative = ltrim($path, '/');
    $info = pathinfo($relative);
    $ext = strtolower($info['extension'] ?? '');
    if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
        $webp = ($info['dirname'] === '.' ? '' : $info['dirname'] . '/') . ($info['filename'] ?? '') . '.webp';
        if (is_file(__DIR__ . '/../' . $webp)) return asset_url($webp);
    }
    return asset_url($relative);
}

function create_optimized_image_variant(string $absolutePath, string $mime, int $maxWidth = 1600, int $quality = 82): ?string {
    if (!extension_loaded('gd') || !is_file($absolutePath)) return null;
    if (!function_exists('imagewebp')) return null;

    $size = @getimagesize($absolutePath);
    if (!$size || empty($size[0]) || empty($size[1])) return null;
    [$width, $height] = [$size[0], $size[1]];

    switch ($mime) {
        case 'image/jpeg':
            if (!function_exists('imagecreatefromjpeg')) return null;
            $src = @imagecreatefromjpeg($absolutePath);
            break;
        case 'image/png':
            if (!function_exists('imagecreatefrompng')) return null;
            $src = @imagecreatefrompng($absolutePath);
            if ($src) { imagepalettetotruecolor($src); imagealphablending($src, true); imagesavealpha($src, true); }
            break;
        case 'image/webp':
            if (!function_exists('imagecreatefromwebp')) return null;
            $src = @imagecreatefromwebp($absolutePath);
            break;
        default:
            return null;
    }
    if (!$src) return null;

    $targetWidth = min($width, $maxWidth);
    $targetHeight = (int)round($height * ($targetWidth / max(1, $width)));
    $dst = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $targetWidth, $targetHeight, $transparent);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

    $webpPath = preg_replace('~\.(jpe?g|png)$~i', '.webp', $absolutePath);
    if (!$webpPath || $webpPath === $absolutePath) {
        $webpPath = preg_replace('~\.webp$~i', '-optimized.webp', $absolutePath) ?: ($absolutePath . '.webp');
    }
    $ok = @imagewebp($dst, $webpPath, max(50, min(95, $quality)));
    imagedestroy($src);
    imagedestroy($dst);
    return $ok && is_file($webpPath) ? $webpPath : null;
}

function optimized_relative_path(string $absolutePath): ?string {
    $root = realpath(__DIR__ . '/..');
    $file = realpath($absolutePath);
    if (!$root || !$file || !str_starts_with($file, $root)) return null;
    return ltrim(str_replace('\\', '/', substr($file, strlen($root))), '/');
}

function detect_upload_mime(array $file): string {
    $tmp = $file['tmp_name'] ?? '';
    if ($tmp === '' || !is_uploaded_file($tmp)) return '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return (string)$finfo->file($tmp);
    }
    return (string)($file['type'] ?? '');
}

function upload_image_file(string $field, string $folder, string $baseName, int $maxBytes = 2097152): ?string {
    if (empty($_FILES[$field]['name'])) return null;
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please select a valid image file.');
    }
    if (($file['size'] ?? 0) > $maxBytes) {
        throw new RuntimeException('Image must be ' . round($maxBytes / 1024 / 1024, 1) . 'MB or less.');
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = detect_upload_mime($file);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG or WEBP images are allowed.');
    }
    $safeBase = slugify($baseName ?: pathinfo((string)$file['name'], PATHINFO_FILENAME));
    $fileName = $safeBase . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $allowed[$mime];
    $relativeDir = trim($folder, '/');
    $absoluteDir = __DIR__ . '/../' . $relativeDir;
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
        throw new RuntimeException('Upload folder could not be created.');
    }
    $dest = $absoluteDir . '/' . $fileName;
    if (!move_uploaded_file((string)$file['tmp_name'], $dest)) {
        throw new RuntimeException('Image upload failed while saving the file.');
    }
    $optimized = create_optimized_image_variant($dest, $mime, 1600, 82);
    $originalRelative = $relativeDir . '/' . $fileName;
    $relativeOptimized = $optimized ? optimized_relative_path($optimized) : null;
    $finalPath = $relativeOptimized ?: $originalRelative;
    if (function_exists('register_media_asset')) {
        register_media_asset([
            'file_path' => $finalPath,
            'original_path' => $originalRelative,
            'mime_type' => $relativeOptimized ? 'image/webp' : $mime,
            'title' => $baseName,
            'alt_text' => $baseName,
            'source' => 'upload',
            'usage_context' => $relativeDir,
        ]);
    }
    return $finalPath;
}

function get_distinct_values(PDO $pdo, string $table, string $column): array {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) return [];
    try {
        $rows = $pdo->query("SELECT DISTINCT {$column} AS value FROM {$table} WHERE {$column} IS NOT NULL AND {$column} <> '' ORDER BY {$column} ASC")->fetchAll();
        return array_values(array_filter(array_map(static fn($row) => (string)($row['value'] ?? ''), $rows)));
    } catch (Throwable $e) { return []; }
}


/* ==========================================================
   V43: Media Library + Reusable Content Blocks helpers
   ========================================================== */
function ensure_phase43_media_content_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    ensure_phase4_blog_schema();
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS media_assets (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(190) DEFAULT NULL,
            alt_text VARCHAR(255) DEFAULT NULL,
            caption TEXT NULL,
            file_path VARCHAR(255) NOT NULL,
            original_path VARCHAR(255) DEFAULT NULL,
            mime_type VARCHAR(120) DEFAULT NULL,
            file_ext VARCHAR(20) DEFAULT NULL,
            file_size INT UNSIGNED DEFAULT 0,
            width INT UNSIGNED DEFAULT NULL,
            height INT UNSIGNED DEFAULT NULL,
            source VARCHAR(80) DEFAULT 'upload',
            usage_context VARCHAR(120) DEFAULT NULL,
            uploaded_by VARCHAR(120) DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_media_path (file_path),
            INDEX idx_media_mime (mime_type),
            INDEX idx_media_source (source),
            INDEX idx_media_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS content_blocks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            block_key VARCHAR(120) NOT NULL UNIQUE,
            placement VARCHAR(80) NOT NULL DEFAULT 'homepage',
            title_en VARCHAR(220) NOT NULL,
            title_bn VARCHAR(220) DEFAULT NULL,
            body_en TEXT NULL,
            body_bn TEXT NULL,
            image VARCHAR(255) DEFAULT NULL,
            button_label_en VARCHAR(120) DEFAULT NULL,
            button_label_bn VARCHAR(120) DEFAULT NULL,
            button_url VARCHAR(255) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_content_blocks_place (placement, is_active, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $stmt = $pdo->query('SHOW COLUMNS FROM blog_posts');
        $columns = array_map(static fn($row) => $row['Field'] ?? '', $stmt->fetchAll());
        if (!in_array('content_blocks_json', $columns, true)) {
            $pdo->exec("ALTER TABLE blog_posts ADD content_blocks_json TEXT NULL AFTER content_bn");
        }
        if (!in_array('editor_notes', $columns, true)) {
            $pdo->exec("ALTER TABLE blog_posts ADD editor_notes TEXT NULL AFTER content_blocks_json");
        }

        $count = (int)$pdo->query('SELECT COUNT(*) FROM content_blocks')->fetchColumn();
        if ($count === 0) {
            $stmt = $pdo->prepare('INSERT INTO content_blocks (block_key, placement, title_en, title_bn, body_en, body_bn, image, button_label_en, button_label_bn, button_url, sort_order, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())');
            foreach (default_content_blocks() as $block) {
                $stmt->execute([
                    $block['block_key'], $block['placement'], $block['title_en'], $block['title_bn'], $block['body_en'], $block['body_bn'], $block['image'], $block['button_label_en'], $block['button_label_bn'], $block['button_url'], (int)$block['sort_order']
                ]);
            }
        }
    } catch (Throwable $e) {
        // Shared-hosting safe: CMS pages remain usable even if ALTER/CREATE is restricted.
    }
}

function default_content_blocks(): array {
    return [
        [
            'block_key' => 'distribution-support',
            'placement' => 'homepage',
            'title_en' => 'Distribution support built for retailers',
            'title_bn' => 'রিটেইলারদের জন্য তৈরি ডিস্ট্রিবিউশন সাপোর্ট',
            'body_en' => 'Use this reusable block to highlight dealer communication, timely supply and market support without editing PHP files.',
            'body_bn' => 'PHP ফাইল এডিট না করেই ডিলার যোগাযোগ, সময়মতো সরবরাহ ও মার্কেট সাপোর্ট তুলে ধরুন।',
            'image' => 'assets/img/hero-atoz-pack.webp',
            'button_label_en' => 'Send Distributor Enquiry',
            'button_label_bn' => 'ডিস্ট্রিবিউটর ইনকোয়ারি দিন',
            'button_url' => '#network',
            'sort_order' => 1,
        ],
        [
            'block_key' => 'catalogue-cta',
            'placement' => 'homepage',
            'title_en' => 'Keep product communication consistent',
            'title_bn' => 'পণ্য যোগাযোগ রাখুন আরও একরকম ও প্রফেশনাল',
            'body_en' => 'Attach catalogue, product cards or announcement copy as reusable blocks and reuse them in homepage or blog stories.',
            'body_bn' => 'ক্যাটালগ, প্রোডাক্ট কার্ড বা ঘোষণা কপি reusable block হিসেবে রেখে homepage বা blog story-তে ব্যবহার করুন।',
            'image' => 'assets/img/hero-signal-pack.webp',
            'button_label_en' => 'Download Catalogue',
            'button_label_bn' => 'ক্যাটালগ ডাউনলোড',
            'button_url' => 'catalogue',
            'sort_order' => 2,
        ],
    ];
}

function register_media_asset(array $data): void {
    ensure_phase43_media_content_schema();
    $pdo = try_db();
    if (!$pdo) return;
    $path = trim((string)($data['file_path'] ?? $data['path'] ?? ''));
    if ($path === '') return;
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $absolute = __DIR__ . '/../' . $path;
    $size = is_file($absolute) ? (int)@filesize($absolute) : (int)($data['file_size'] ?? 0);
    $imageInfo = is_file($absolute) ? @getimagesize($absolute) : null;
    $width = $imageInfo[0] ?? ($data['width'] ?? null);
    $height = $imageInfo[1] ?? ($data['height'] ?? null);
    $mime = (string)($data['mime_type'] ?? ($imageInfo['mime'] ?? ''));
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $title = trim((string)($data['title'] ?? pathinfo($path, PATHINFO_FILENAME)));
    try {
        $stmt = $pdo->prepare('INSERT INTO media_assets (title, alt_text, caption, file_path, original_path, mime_type, file_ext, file_size, width, height, source, usage_context, uploaded_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE title = COALESCE(NULLIF(VALUES(title), \"\"), title), alt_text = COALESCE(NULLIF(VALUES(alt_text), \"\"), alt_text), caption = COALESCE(NULLIF(VALUES(caption), \"\"), caption), original_path = COALESCE(NULLIF(VALUES(original_path), \"\"), original_path), mime_type = VALUES(mime_type), file_ext = VALUES(file_ext), file_size = VALUES(file_size), width = VALUES(width), height = VALUES(height), source = VALUES(source), usage_context = COALESCE(NULLIF(VALUES(usage_context), \"\"), usage_context), uploaded_by = COALESCE(NULLIF(VALUES(uploaded_by), \"\"), uploaded_by), updated_at = NOW()');
        $stmt->execute([
            $title,
            trim((string)($data['alt_text'] ?? '')),
            trim((string)($data['caption'] ?? '')),
            $path,
            trim((string)($data['original_path'] ?? '')),
            $mime,
            $ext,
            $size,
            $width !== null ? (int)$width : null,
            $height !== null ? (int)$height : null,
            substr((string)($data['source'] ?? 'upload'), 0, 80),
            substr((string)($data['usage_context'] ?? ''), 0, 120),
            substr((string)($data['uploaded_by'] ?? ($_SESSION['admin_username'] ?? 'admin')), 0, 120),
        ]);
    } catch (Throwable $e) {}
}

function scan_uploads_into_media_library(): int {
    ensure_phase43_media_content_schema();
    $root = realpath(__DIR__ . '/../uploads');
    if (!$root || !is_dir($root)) return 0;
    $allowed = ['jpg','jpeg','png','webp','gif','svg','pdf'];
    $count = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, $allowed, true)) continue;
        $absolute = $file->getPathname();
        $relative = optimized_relative_path($absolute);
        if (!$relative) continue;
        $mime = '';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string)$finfo->file($absolute);
        }
        register_media_asset([
            'file_path' => $relative,
            'title' => pathinfo($relative, PATHINFO_FILENAME),
            'mime_type' => $mime,
            'source' => 'sync',
            'usage_context' => dirname($relative),
        ]);
        $count++;
    }
    return $count;
}

function get_media_assets(array $filters = [], int $limit = 60): array {
    ensure_phase43_media_content_schema();
    $pdo = try_db();
    if (!$pdo) return [];
    $where = [];
    $params = [];
    $q = trim((string)($filters['q'] ?? ''));
    $type = trim((string)($filters['type'] ?? ''));
    if ($q !== '') {
        $like = '%' . $q . '%';
        $where[] = '(title LIKE ? OR alt_text LIKE ? OR caption LIKE ? OR file_path LIKE ? OR usage_context LIKE ?)';
        array_push($params, $like, $like, $like, $like, $like);
    }
    if ($type === 'images') $where[] = "mime_type LIKE 'image/%'";
    if ($type === 'documents') $where[] = "mime_type NOT LIKE 'image/%'";
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    try {
        $stmt = $pdo->prepare("SELECT * FROM media_assets {$whereSql} ORDER BY created_at DESC, id DESC LIMIT " . max(12, min(240, $limit)));
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) { return []; }
}

function safe_delete_upload_file(string $relative): bool {
    $relative = ltrim(str_replace('\\', '/', trim($relative)), '/');
    if ($relative === '' || !str_starts_with($relative, 'uploads/')) return false;
    $root = realpath(__DIR__ . '/..');
    $path = realpath(__DIR__ . '/../' . $relative);
    if (!$root || !$path || !str_starts_with($path, $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR)) return false;
    return is_file($path) ? @unlink($path) : false;
}

function get_content_blocks(?string $placement = null, bool $activeOnly = true): array {
    ensure_phase43_media_content_schema();
    $pdo = try_db();
    if (!$pdo) {
        $items = default_content_blocks();
        return $placement ? array_values(array_filter($items, static fn($item) => ($item['placement'] ?? '') === $placement)) : $items;
    }
    $where = [];
    $params = [];
    if ($placement !== null && $placement !== '') { $where[] = 'placement = ?'; $params[] = $placement; }
    if ($activeOnly) $where[] = 'is_active = 1';
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    try {
        $stmt = $pdo->prepare("SELECT * FROM content_blocks {$whereSql} ORDER BY sort_order ASC, id DESC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) { return []; }
}

function get_content_block_by_key(string $key): ?array {
    ensure_phase43_media_content_schema();
    $key = trim($key);
    if ($key === '') return null;
    $pdo = try_db();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM content_blocks WHERE block_key = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            if ($row) return $row;
        } catch (Throwable $e) {}
    }
    foreach (default_content_blocks() as $block) if (($block['block_key'] ?? '') === $key) return $block;
    return null;
}

function content_block_html(array $block, string $class = ''): string {
    $titleEn = trim((string)($block['title_en'] ?? ''));
    if ($titleEn === '') return '';
    $titleBn = trim((string)($block['title_bn'] ?? '')) ?: $titleEn;
    $bodyEn = trim((string)($block['body_en'] ?? ''));
    $bodyBn = trim((string)($block['body_bn'] ?? '')) ?: $bodyEn;
    $image = trim((string)($block['image'] ?? ''));
    $buttonLabelEn = trim((string)($block['button_label_en'] ?? ''));
    $buttonLabelBn = trim((string)($block['button_label_bn'] ?? '')) ?: $buttonLabelEn;
    $buttonUrl = trim((string)($block['button_url'] ?? ''));
    if ($buttonUrl !== '' && !preg_match('~^(https?://|mailto:|tel:|#)~i', $buttonUrl)) $buttonUrl = base_url($buttonUrl);
    $classes = trim('content-block-card-v43 ' . $class);
    ob_start(); ?>
    <article class="<?= e($classes) ?>">
        <?php if($image !== ''): ?><div class="content-block-media-v43"><img src="<?= e(optimized_image_url($image)) ?>" alt="<?= e($titleEn) ?>" loading="lazy"></div><?php endif; ?>
        <div class="content-block-copy-v43">
            <span class="eyebrow" data-en="Content Block" data-bn="কনটেন্ট ব্লক">Content Block</span>
            <h3 data-en="<?= e($titleEn) ?>" data-bn="<?= e($titleBn) ?>"><?= e($titleEn) ?></h3>
            <?php if($bodyEn !== ''): ?><p data-en="<?= e($bodyEn) ?>" data-bn="<?= e($bodyBn) ?>"><?= e($bodyEn) ?></p><?php endif; ?>
            <?php if($buttonLabelEn !== '' && $buttonUrl !== ''): ?><a class="btn btn-ghost" href="<?= e($buttonUrl) ?>" data-en="<?= e($buttonLabelEn) ?>" data-bn="<?= e($buttonLabelBn) ?>"><?= e($buttonLabelEn) ?></a><?php endif; ?>
        </div>
    </article>
    <?php return trim(ob_get_clean());
}

function render_content_blocks_section(string $placement, string $title = 'Reusable Brand Highlights'): string {
    $blocks = get_content_blocks($placement, true);
    if (!$blocks) return '';
    ob_start(); ?>
    <section class="section-pad-sm content-blocks-section-v43">
        <div class="container">
            <div class="section-head compact-head reveal">
                <span class="eyebrow" data-en="CMS Blocks" data-bn="CMS ব্লক">CMS Blocks</span>
                <h2 data-en="<?= e($title) ?>" data-bn="<?= e($title) ?>"><?= e($title) ?></h2>
                <p data-en="Reusable blocks controlled from admin, useful for campaign, trade and catalogue messages." data-bn="ক্যাম্পেইন, ট্রেড ও ক্যাটালগ মেসেজের জন্য অ্যাডমিন থেকে নিয়ন্ত্রিত reusable blocks।">Reusable blocks controlled from admin, useful for campaign, trade and catalogue messages.</p>
            </div>
            <div class="content-blocks-grid-v43 reveal">
                <?php foreach($blocks as $block): ?><?= content_block_html($block) ?><?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php return trim(ob_get_clean());
}

function render_content_block_by_key(string $key): string {
    $block = get_content_block_by_key($key);
    return $block ? content_block_html($block, 'inline-blog-block-v43') : '';
}

function content_block_key_options(): array {
    return array_values(array_map(static fn($block) => (string)($block['block_key'] ?? ''), get_content_blocks(null, false)));
}

/* ==========================================================
   V31: Phase 2 CMS helpers — editable site content, team and timeline
   ========================================================== */
function phase2_setting_defaults(): array {
    return [
        'hero_pill_en' => 'Trusted Protection for Generations',
        'hero_pill_bn' => 'প্রজন্মের পর প্রজন্ম বিশ্বস্ত সুরক্ষা',
        'hero_title_line1_en' => 'No more mosquito bites,',
        'hero_title_line1_bn' => 'মশার যন্ত্রণা আর নয়,',
        'hero_title_line2_en' => 'Peaceful every night.',
        'hero_title_line2_bn' => 'রাত হোক শান্তিময়।',
        'hero_subtitle_en' => 'A TO Z and SIGNAL mosquito coils are made for dependable daily protection, cleaner comfort and confident family use across Bangladesh.',
        'hero_subtitle_bn' => "প্রতিটি পরিবারের নির্ভরযোগ্য দৈনন্দিন সুরক্ষা, স্বস্তিদায়ক ব্যবহার এবং নিশ্চিন্ত নিরাপত্তার কথা মাথায় রেখেই তৈরি করা হয়েছে 'A TO Z' এবং 'SIGNAL' মশার কয়েল।",
        'hero_primary_cta_en' => 'View Products',
        'hero_primary_cta_bn' => 'পণ্য দেখুন',
        'hero_secondary_cta_en' => 'Become Distributor',
        'hero_secondary_cta_bn' => 'ডিস্ট্রিবিউটর হোন',
        'home_seo_title' => 'Reliable Mosquito Protection for Bangladesh',
        'home_seo_description' => 'Official A TO Z and SIGNAL mosquito coil website featuring premium products, protection technology and nationwide distributor support across Bangladesh.',
        'about_eyebrow_en' => 'About Our Brands',
        'about_eyebrow_bn' => 'আমাদের ব্র্যান্ড সম্পর্কে',
        'about_title_en' => 'Two trusted brands, one disciplined distribution vision.',
        'about_title_bn' => 'দুটি বিশ্বস্ত ব্র্যান্ড, একটি সুশৃঙ্খল ডিস্ট্রিবিউশন ভিশন।',
        'about_intro_en' => 'A TO Z and SIGNAL are built for reliable household protection, retailer confidence and a stronger national distribution network.',
        'about_intro_bn' => 'A TO Z এবং SIGNAL নির্ভরযোগ্য পারিবারিক সুরক্ষা, রিটেইলার আস্থা এবং শক্তিশালী জাতীয় ডিস্ট্রিবিউশন নেটওয়ার্কের জন্য তৈরি।',
        'mission_text_en' => 'To deliver dependable consumer products through disciplined distribution, strong trade support and consistent product availability.',
        'mission_text_bn' => 'সুশৃঙ্খল ডিস্ট্রিবিউশন, শক্তিশালী ট্রেড সাপোর্ট এবং ধারাবাহিক পণ্য প্রাপ্যতার মাধ্যমে নির্ভরযোগ্য ভোক্তা পণ্য সরবরাহ করা।',
        'vision_text_en' => 'To become one of Bangladesh’s trusted FMCG distribution partners by building brands that retailers recommend and households rely on.',
        'vision_text_bn' => 'রিটেইলাররা সুপারিশ করে এবং পরিবারগুলো ভরসা করে—এমন ব্র্যান্ড গড়ে বাংলাদেশের বিশ্বস্ত FMCG ডিস্ট্রিবিউশন পার্টার হওয়া।',
        'action_plan_en' => "Strengthen distributor and dealer communication\nImprove product visibility across retail outlets\nSupport timely supply and market execution\nBuild long-term brand confidence across Bangladesh",
        'action_plan_bn' => "ডিস্ট্রিবিউটর ও ডিলার যোগাযোগ শক্তিশালী করা\nরিটেইল আউটলেটে পণ্যের দৃশ্যমানতা বৃদ্ধি করা\nসময়মতো সরবরাহ ও বাজার বাস্তবায়ন নিশ্চিত করা\nবাংলাদেশজুড়ে দীর্ঘমেয়াদি ব্র্যান্ড আস্থা তৈরি করা",
        'footer_description_en' => 'A TO Z and SIGNAL provide dependable mosquito protection solutions trusted by households, retailers and distribution partners across Bangladesh.',
        'footer_description_bn' => 'A TO Z এবং SIGNAL বাংলাদেশজুড়ে পরিবার, রিটেইলার ও ডিস্ট্রিবিউশন পার্টনারদের জন্য নির্ভরযোগ্য মশা সুরক্ষা সমাধান প্রদান করে।',
        'catalogue_title_en' => 'A TO Z & SIGNAL Product Catalogue',
        'catalogue_title_bn' => 'এ টু জেড ও সিগনাল পণ্য ক্যাটালগ',
        'catalogue_version' => '2026 Trade Edition',
        'catalogue_updated_at' => '2026-06-19',
        'catalogue_note_en' => 'Download the latest product catalogue for trade and distribution reference.',
        'catalogue_note_bn' => 'ট্রেড ও ডিস্ট্রিবিউশন রেফারেন্সের জন্য সর্বশেষ পণ্য ক্যাটালগ ডাউনলোড করুন.',
        'google_map_url' => '',
        'meta_pixel_id' => '',
        'google_analytics_id' => '',
    ];
}

function ensure_phase2_cms_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_team (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name_en VARCHAR(160) NOT NULL,
            name_bn VARCHAR(160) DEFAULT NULL,
            role_en VARCHAR(160) DEFAULT NULL,
            role_bn VARCHAR(160) DEFAULT NULL,
            intro_en TEXT NULL,
            intro_bn TEXT NULL,
            image VARCHAR(255) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_team_active_sort (is_active, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS company_timeline (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            year_label VARCHAR(40) NOT NULL,
            title_en VARCHAR(220) NOT NULL,
            title_bn VARCHAR(220) DEFAULT NULL,
            excerpt_en TEXT NULL,
            excerpt_bn TEXT NULL,
            image VARCHAR(255) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_timeline_active_sort (is_active, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        foreach (phase2_setting_defaults() as $key => $value) {
            $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_key = setting_key');
            $stmt->execute([$key, $value]);
        }

        $teamCount = (int)$pdo->query('SELECT COUNT(*) FROM site_team')->fetchColumn();
        if ($teamCount === 0) {
            $defaults = default_team_members();
            $stmt = $pdo->prepare('INSERT INTO site_team (name_en, name_bn, role_en, role_bn, intro_en, intro_bn, image, sort_order, is_active, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,1,NOW(),NOW())');
            foreach ($defaults as $member) {
                $stmt->execute([$member['name_en'], $member['name_bn'], $member['role_en'], $member['role_bn'], $member['intro_en'], $member['intro_bn'], $member['image'], (int)$member['sort_order']]);
            }
        }

        $timelineCount = (int)$pdo->query('SELECT COUNT(*) FROM company_timeline')->fetchColumn();
        if ($timelineCount === 0) {
            $defaults = default_timeline_entries();
            $stmt = $pdo->prepare('INSERT INTO company_timeline (year_label, title_en, title_bn, excerpt_en, excerpt_bn, image, sort_order, is_active, created_at, updated_at) VALUES (?,?,?,?,?,?,?,1,NOW(),NOW())');
            foreach ($defaults as $entry) {
                $stmt->execute([$entry['year_label'], $entry['title_en'], $entry['title_bn'], $entry['excerpt_en'], $entry['excerpt_bn'], $entry['image'], (int)$entry['sort_order']]);
            }
        }
    } catch (Throwable $e) {
        // Shared hosting compatibility: frontend stays online with fallback data.
    }
}

function cms_setting(array $settings, string $key): string {
    $defaults = phase2_setting_defaults();
    return (string)($settings[$key] ?? $defaults[$key] ?? '');
}

function save_site_settings(array $keys, array $source): void {
    $pdo = db();
    $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()');
    foreach ($keys as $key) {
        $stmt->execute([$key, trim((string)($source[$key] ?? ''))]);
    }
}

function upload_document_file(string $field, string $folder, string $baseName, int $maxBytes = 8388608): ?string {
    if (empty($_FILES[$field]['name'])) return null;
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Document upload failed. Please select a valid PDF or image file.');
    }
    if (($file['size'] ?? 0) > $maxBytes) {
        throw new RuntimeException('Document must be ' . round($maxBytes / 1024 / 1024, 1) . 'MB or less.');
    }
    $tmp = (string)($file['tmp_name'] ?? '');
    $mime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmp);
    } else {
        $mime = (string)($file['type'] ?? '');
    }
    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only PDF, JPG, PNG or WEBP catalogue files are allowed.');
    }
    $safeBase = slugify($baseName ?: pathinfo((string)$file['name'], PATHINFO_FILENAME));
    $fileName = $safeBase . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $allowed[$mime];
    $relativeDir = trim($folder, '/');
    $absoluteDir = __DIR__ . '/../' . $relativeDir;
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
        throw new RuntimeException('Catalogue folder could not be created.');
    }
    $dest = $absoluteDir . '/' . $fileName;
    if (!move_uploaded_file($tmp, $dest)) {
        throw new RuntimeException('Catalogue upload failed while saving the file.');
    }
    return $relativeDir . '/' . $fileName;
}

function default_team_members(): array {
    return [
        ['name_en'=>'MD. NURUZZAMAN','name_bn'=>'মোঃ নুরুজ্জামান','role_en'=>'Managing Director','role_bn'=>'ম্যানেজিং ডিরেক্টর','image'=>'uploads/team/managing-director.png','intro_en'=>'Leads the company with a strong focus on brand trust, distribution discipline and long-term FMCG business growth across Bangladesh.','intro_bn'=>'ব্র্যান্ডের আস্থা, ডিস্ট্রিবিউশন শৃঙ্খলা এবং বাংলাদেশজুড়ে দীর্ঘমেয়াদি FMCG ব্যবসা বৃদ্ধির লক্ষ্যে প্রতিষ্ঠানকে নেতৃত্ব দেন।','sort_order'=>1],
        ['name_en'=>'MD. RAFIQUL ISLAM','name_bn'=>'মোঃ রফিকুল ইসলাম','role_en'=>'Head of Admin','role_bn'=>'হেড অফ অ্যাডমিন','image'=>'uploads/team/head-of-admin.png','intro_en'=>'Oversees administrative coordination, operational support and internal process control for reliable business execution.','intro_bn'=>'নির্ভরযোগ্য ব্যবসায়িক বাস্তবায়নের জন্য প্রশাসনিক সমন্বয়, অপারেশনাল সাপোর্ট এবং অভ্যন্তরীণ প্রক্রিয়া নিয়ন্ত্রণ করেন।','sort_order'=>2],
        ['name_en'=>'GOLAM MOSTAFA','name_bn'=>'গোলাম মোস্তফা','role_en'=>'Chief Operating Officer','role_bn'=>'চিফ অপারেটিং অফিসার','image'=>'uploads/team/chief-operating-officer.png','intro_en'=>'Drives day-to-day operations, product movement, supply coordination and structured execution across the business network.','intro_bn'=>'দৈনন্দিন অপারেশন, পণ্য চলাচল, সরবরাহ সমন্বয় এবং ব্যবসায়িক নেটওয়ার্কজুড়ে সুশৃঙ্খল বাস্তবায়ন পরিচালনা করেন।','sort_order'=>3],
        ['name_en'=>'MD. ABDUL ALIM','name_bn'=>'মোঃ আব্দুল আলিম','role_en'=>'National Sales Manager','role_bn'=>'ন্যাশনাল সেলস ম্যানেজার','image'=>'uploads/team/national-sales-manager.png','intro_en'=>'Manages sales team coordination, distributor communication and market coverage planning for stronger retail presence.','intro_bn'=>'শক্তিশালী রিটেইল উপস্থিতির জন্য সেলস টিম সমন্বয়, ডিস্ট্রিবিউটর যোগাযোগ এবং বাজার কাভারেজ পরিকল্পনা পরিচালনা করেন।','sort_order'=>4],
    ];
}

function get_team_members(bool $activeOnly = true): array {
    ensure_phase2_cms_schema();
    $pdo = try_db();
    if (!$pdo) return default_team_members();
    try {
        $sql = 'SELECT * FROM site_team ' . ($activeOnly ? 'WHERE is_active = 1 ' : '') . 'ORDER BY sort_order ASC, id ASC';
        $rows = $pdo->query($sql)->fetchAll();
        return $rows ?: default_team_members();
    } catch (Throwable $e) { return default_team_members(); }
}

function default_timeline_entries(): array {
    return [
        ['year_label'=>'2017','title_en'=>'1st Sales Meet','title_bn'=>'১ম সেলস মিট','excerpt_en'=>'The early sales culture and field-force alignment started taking stronger shape.','excerpt_bn'=>'প্রাথমিক সেলস কালচার ও ফিল্ড-ফোর্স সমন্বয় আরও শক্তিশালী হতে শুরু করে।','image'=>'uploads/blog/sales-meet-2017.jpeg','sort_order'=>1],
        ['year_label'=>'2018','title_en'=>'Launching Ceremony','title_bn'=>'লঞ্চিং সিরেমনি','excerpt_en'=>'A brand-building launch moment that supported channel visibility and retail confidence.','excerpt_bn'=>'চ্যানেল দৃশ্যমানতা ও রিটেইল আস্থা বৃদ্ধির জন্য একটি ব্র্যান্ড-বিল্ডিং লঞ্চ মুহূর্ত।','image'=>'uploads/blog/launching-ceremony-2018.jpeg','sort_order'=>2],
        ['year_label'=>'2019','title_en'=>'Team Growth','title_bn'=>'টিম গ্রোথ','excerpt_en'=>'Team activities helped build stronger distribution discipline and market execution.','excerpt_bn'=>'টিম কার্যক্রম ডিস্ট্রিবিউশন শৃঙ্খলা ও বাজার বাস্তবায়নকে আরও শক্তিশালী করেছে।','image'=>'uploads/blog/sales-meet-2019-team.jpeg','sort_order'=>3],
        ['year_label'=>'2024','title_en'=>'SIGNAL Spider Launch','title_bn'=>'সিগনাল স্পাইডার লঞ্চ','excerpt_en'=>'A modern product story introduced stronger low-smoke brand communication.','excerpt_bn'=>'আধুনিক পণ্য গল্প কম ধোঁয়া ব্র্যান্ড কমিউনিকেশনকে আরও শক্তিশালী করেছে।','image'=>'uploads/blog/sales-meet-2024-team.jpeg','sort_order'=>4],
        ['year_label'=>'2025','title_en'=>'6th Sales Meet','title_bn'=>'৬ষ্ঠ সেলস মিট','excerpt_en'=>'A renewed national sales focus for channel growth and product visibility.','excerpt_bn'=>'চ্যানেল গ্রোথ ও পণ্য দৃশ্যমানতার জন্য নতুন জাতীয় সেলস ফোকাস।','image'=>'uploads/blog/sales-meet-2025-stage.jpg','sort_order'=>5],
    ];
}

function get_timeline_entries(bool $activeOnly = true): array {
    ensure_phase2_cms_schema();
    $pdo = try_db();
    if (!$pdo) return default_timeline_entries();
    try {
        $sql = 'SELECT * FROM company_timeline ' . ($activeOnly ? 'WHERE is_active = 1 ' : '') . 'ORDER BY sort_order ASC, id ASC';
        $rows = $pdo->query($sql)->fetchAll();
        return $rows ?: default_timeline_entries();
    } catch (Throwable $e) { return default_timeline_entries(); }
}

function split_lines(?string $value): array {
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$value))));
}


/* ==========================================================
   V32: Phase 3 product system helpers — specs, galleries, FAQ and comparisons
   ========================================================== */
function ensure_phase3_product_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $stmt = $pdo->query('SHOW COLUMNS FROM products');
        $columns = array_map(static fn($row) => $row['Field'] ?? '', $stmt->fetchAll());
        $add = [
            'pack_size' => "ALTER TABLE products ADD pack_size VARCHAR(120) NULL AFTER protection_hours",
            'carton_size' => "ALTER TABLE products ADD carton_size VARCHAR(120) NULL AFTER pack_size",
            'smoke_type' => "ALTER TABLE products ADD smoke_type VARCHAR(120) NULL AFTER carton_size",
            'fragrance' => "ALTER TABLE products ADD fragrance VARCHAR(120) NULL AFTER smoke_type",
            'room_size' => "ALTER TABLE products ADD room_size VARCHAR(120) NULL AFTER fragrance",
            'formula_type' => "ALTER TABLE products ADD formula_type VARCHAR(160) NULL AFTER room_size",
            'burn_time' => "ALTER TABLE products ADD burn_time VARCHAR(120) NULL AFTER formula_type",
            'usage_instructions_en' => "ALTER TABLE products ADD usage_instructions_en TEXT NULL AFTER short_description_bn",
            'usage_instructions_bn' => "ALTER TABLE products ADD usage_instructions_bn TEXT NULL AFTER usage_instructions_en",
            'safety_instructions_en' => "ALTER TABLE products ADD safety_instructions_en TEXT NULL AFTER usage_instructions_bn",
            'safety_instructions_bn' => "ALTER TABLE products ADD safety_instructions_bn TEXT NULL AFTER safety_instructions_en",
            'gallery_json' => "ALTER TABLE products ADD gallery_json MEDIUMTEXT NULL AFTER image",
            'faq_json' => "ALTER TABLE products ADD faq_json MEDIUMTEXT NULL AFTER gallery_json",
            'catalogue_url' => "ALTER TABLE products ADD catalogue_url VARCHAR(255) NULL AFTER faq_json",
            'comparison_highlight' => "ALTER TABLE products ADD comparison_highlight VARCHAR(180) NULL AFTER catalogue_url",
            'seo_title' => "ALTER TABLE products ADD seo_title VARCHAR(220) NULL AFTER accent_color",
            'seo_description' => "ALTER TABLE products ADD seo_description TEXT NULL AFTER seo_title",
        ];
        foreach ($add as $column => $sql) {
            if (!in_array($column, $columns, true)) {
                $pdo->exec($sql);
            }
        }
        try { $pdo->exec('CREATE INDEX idx_products_category ON products (category)'); } catch (Throwable $e) {}
    } catch (Throwable $e) {
        // Shared-hosting safe: old databases keep running with frontend fallbacks.
    }
}

function product_spec_fields(): array {
    return [
        'protection_hours' => ['label_en' => 'Protection', 'label_bn' => 'সুরক্ষা সময়'],
        'pack_size' => ['label_en' => 'Pack Size', 'label_bn' => 'প্যাক সাইজ'],
        'carton_size' => ['label_en' => 'Carton Size', 'label_bn' => 'কার্টন সাইজ'],
        'smoke_type' => ['label_en' => 'Smoke Type', 'label_bn' => 'ধোঁয়ার ধরন'],
        'fragrance' => ['label_en' => 'Fragrance', 'label_bn' => 'সুগন্ধ'],
        'room_size' => ['label_en' => 'Best For', 'label_bn' => 'ব্যবহার উপযোগী'],
        'formula_type' => ['label_en' => 'Formula', 'label_bn' => 'ফর্মুলা'],
        'burn_time' => ['label_en' => 'Burning', 'label_bn' => 'বার্নিং'],
    ];
}

function product_specs(array $product): array {
    $rows = [];
    foreach (product_spec_fields() as $key => $meta) {
        $value = trim((string)($product[$key] ?? ''));
        if ($value !== '') {
            $rows[] = ['key' => $key, 'label_en' => $meta['label_en'], 'label_bn' => $meta['label_bn'], 'value' => $value];
        }
    }
    if (!$rows) {
        $rows[] = ['key' => 'category', 'label_en' => 'Category', 'label_bn' => 'ক্যাটাগরি', 'value' => (string)($product['category'] ?? 'Mosquito Coil')];
        $rows[] = ['key' => 'brand', 'label_en' => 'Brand', 'label_bn' => 'ব্র্যান্ড', 'value' => (string)($product['brand'] ?? '')];
    }
    return $rows;
}

function normalize_json_list($value): array {
    if (is_array($value)) return array_values(array_filter(array_map('trim', $value)));
    $decoded = json_decode((string)$value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return array_values(array_filter(array_map('trim', $decoded)));
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', (string)$value))));
}

function product_gallery(array $product): array {
    $gallery = normalize_json_list($product['gallery_json'] ?? '');
    $main = trim((string)($product['image'] ?? ''));
    if ($main !== '') array_unshift($gallery, $main);
    return array_values(array_unique(array_filter($gallery)));
}

function parse_faq_text(string $text): array {
    $items = [];
    $blocks = preg_split('/\n\s*\n/', trim($text));
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') continue;
        if (strpos($block, '|') !== false) {
            [$q, $a] = array_pad(explode('|', $block, 2), 2, '');
        } else {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $block))));
            $q = $lines[0] ?? '';
            $a = implode(' ', array_slice($lines, 1));
        }
        $q = trim($q); $a = trim($a);
        if ($q !== '' && $a !== '') $items[] = ['q' => $q, 'a' => $a];
    }
    return $items;
}

function product_faqs(array $product): array {
    $decoded = json_decode((string)($product['faq_json'] ?? ''), true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return array_values(array_filter($decoded, static fn($item) => is_array($item) && trim((string)($item['q'] ?? '')) !== '' && trim((string)($item['a'] ?? '')) !== ''));
    }
    return [];
}

function faqs_to_text($value): string {
    $items = is_array($value) ? $value : product_faqs(['faq_json' => $value]);
    $blocks = [];
    foreach ($items as $item) {
        $q = trim((string)($item['q'] ?? ''));
        $a = trim((string)($item['a'] ?? ''));
        if ($q !== '' && $a !== '') $blocks[] = $q . "\n" . $a;
    }
    return implode("\n\n", $blocks);
}

function product_default_usage(array $product): string {
    return 'Place the coil safely on a proper stand, light the tip carefully, keep it away from children and flammable materials, and use in a ventilated room.';
}

function product_default_safety(array $product): string {
    return 'Use only as directed. Keep away from food, curtains, papers, bedding and direct touch. Wash hands after handling and keep out of reach of children.';
}

function related_products(array $product, int $limit = 3): array {
    $products = get_products(true);
    $sameBrand = [];
    $others = [];
    foreach ($products as $p) {
        if ((int)($p['id'] ?? 0) === (int)($product['id'] ?? 0)) continue;
        if (($p['brand'] ?? '') === ($product['brand'] ?? '')) $sameBrand[] = $p;
        else $others[] = $p;
    }
    return array_slice(array_merge($sameBrand, $others), 0, $limit);
}

function upload_multiple_image_files(string $field, string $folder, string $baseName, int $maxBytes = 2097152): array {
    if (empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) return [];
    $paths = [];
    $count = count($_FILES[$field]['name']);
    for ($i = 0; $i < $count; $i++) {
        if (empty($_FILES[$field]['name'][$i])) continue;
        $file = [
            'name' => $_FILES[$field]['name'][$i],
            'type' => $_FILES[$field]['type'][$i] ?? '',
            'tmp_name' => $_FILES[$field]['tmp_name'][$i] ?? '',
            'error' => $_FILES[$field]['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $_FILES[$field]['size'][$i] ?? 0,
        ];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
        if (($file['size'] ?? 0) > $maxBytes) throw new RuntimeException('Each gallery image must be ' . round($maxBytes / 1024 / 1024, 1) . 'MB or less.');
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = detect_upload_mime($file);
        if (!isset($allowed[$mime])) throw new RuntimeException('Gallery images must be JPG, PNG or WEBP.');
        $safeBase = slugify($baseName ?: pathinfo((string)$file['name'], PATHINFO_FILENAME));
        $fileName = $safeBase . '-gallery-' . ($i + 1) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $allowed[$mime];
        $relativeDir = trim($folder, '/');
        $absoluteDir = __DIR__ . '/../' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) throw new RuntimeException('Gallery folder could not be created.');
        $dest = $absoluteDir . '/' . $fileName;
        if (!move_uploaded_file((string)$file['tmp_name'], $dest)) throw new RuntimeException('Gallery image upload failed while saving.');
        $optimized = create_optimized_image_variant($dest, $mime, 1600, 82);
        $originalRelative = $relativeDir . '/' . $fileName;
        $relativeOptimized = $optimized ? optimized_relative_path($optimized) : null;
        $finalPath = $relativeOptimized ?: $originalRelative;
        if (function_exists('register_media_asset')) {
            register_media_asset([
                'file_path' => $finalPath,
                'original_path' => $originalRelative,
                'mime_type' => $relativeOptimized ? 'image/webp' : $mime,
                'title' => $baseName . ' gallery',
                'alt_text' => $baseName,
                'source' => 'gallery-upload',
                'usage_context' => $relativeDir,
            ]);
        }
        $paths[] = $finalPath;
    }
    return $paths;
}


/* ==========================================================
   V33: Phase 4 Blog / Archive system helpers — category, tags,
   featured story, search/filter, pagination, share and SEO
   ========================================================== */
function ensure_phase4_blog_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    ensure_blog_table();
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $stmt = $pdo->query('SHOW COLUMNS FROM blog_posts');
        $columns = array_map(static fn($row) => $row['Field'] ?? '', $stmt->fetchAll());
        $add = [
            'category' => "ALTER TABLE blog_posts ADD category VARCHAR(120) NULL AFTER slug",
            'tags' => "ALTER TABLE blog_posts ADD tags VARCHAR(255) NULL AFTER category",
            'is_featured' => "ALTER TABLE blog_posts ADD is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER sort_order",
            'seo_title' => "ALTER TABLE blog_posts ADD seo_title VARCHAR(220) NULL AFTER is_published",
            'seo_description' => "ALTER TABLE blog_posts ADD seo_description TEXT NULL AFTER seo_title",
        ];
        foreach ($add as $column => $sql) {
            if (!in_array($column, $columns, true)) {
                $pdo->exec($sql);
            }
        }
        try { $pdo->exec('CREATE INDEX idx_blog_category ON blog_posts (category)'); } catch (Throwable $e) {}
        try { $pdo->exec('CREATE INDEX idx_blog_featured ON blog_posts (is_featured, is_published, published_at)'); } catch (Throwable $e) {}
    } catch (Throwable $e) {
        // Keep public pages running on restricted shared-hosting databases.
    }
}

function blog_default_category(): string {
    return 'Company Archive';
}

function blog_categories(): array {
    ensure_phase4_blog_schema();
    $pdo = try_db();
    if (!$pdo) return ['Company Archive', 'Sales Meet', 'Product Launch', 'Brand Update'];
    try {
        $rows = $pdo->query("SELECT DISTINCT category FROM blog_posts WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC")->fetchAll();
        $values = array_values(array_filter(array_map(static fn($row) => (string)($row['category'] ?? ''), $rows)));
        return $values ?: ['Company Archive', 'Sales Meet', 'Product Launch', 'Brand Update'];
    } catch (Throwable $e) {
        return ['Company Archive', 'Sales Meet', 'Product Launch', 'Brand Update'];
    }
}

function blog_years(): array {
    ensure_phase4_blog_schema();
    $pdo = try_db();
    if (!$pdo) return ['2025','2024','2021','2018','2017'];
    try {
        $rows = $pdo->query('SELECT DISTINCT YEAR(published_at) AS y FROM blog_posts WHERE published_at IS NOT NULL ORDER BY y DESC')->fetchAll();
        return array_values(array_filter(array_map(static fn($row) => (string)($row['y'] ?? ''), $rows)));
    } catch (Throwable $e) { return []; }
}

function parse_blog_tags(?string $tags): array {
    return array_values(array_unique(array_filter(array_map(static fn($v) => trim((string)$v), preg_split('/[,#]+/', (string)$tags)))));
}

function blog_content_html(?string $content): string {
    $content = trim((string)$content);
    if ($content === '') return '';
    $blocks = preg_split('/\n\s*\n/', $content);
    $html = '';
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') continue;

        if (preg_match('/^\[block\s*:\s*([^\]]+)\]$/i', $block, $m)) {
            $html .= function_exists('render_content_block_by_key') ? render_content_block_by_key(trim($m[1])) : '';
            continue;
        }
        if (preg_match('/^\[image\s*:\s*([^|\]]+)(?:\|([^\]]+))?\]$/i', $block, $m)) {
            $src = trim($m[1]);
            $alt = trim((string)($m[2] ?? 'A TO Z & SIGNAL'));
            if ($src !== '') $html .= '<figure class="blog-rich-image-v43"><img src="' . e(optimized_image_url($src)) . '" alt="' . e($alt) . '" loading="lazy"><figcaption>' . e($alt) . '</figcaption></figure>';
            continue;
        }
        if (preg_match('/^\[button\s*:\s*([^|\]]+)\|([^\]]+)\]$/i', $block, $m)) {
            $label = trim($m[1]);
            $url = trim($m[2]);
            if ($url !== '' && !preg_match('~^(https?://|mailto:|tel:|#)~i', $url)) $url = base_url($url);
            if ($label !== '' && $url !== '') $html .= '<p class="blog-rich-cta-v43"><a class="btn btn-red" href="' . e($url) . '">' . e($label) . '</a></p>';
            continue;
        }
        if (preg_match('/^###\s+(.+)$/', $block, $m)) {
            $html .= '<h3>' . e(trim($m[1])) . '</h3>';
            continue;
        }
        if (preg_match('/^##\s+(.+)$/', $block, $m)) {
            $html .= '<h2>' . e(trim($m[1])) . '</h2>';
            continue;
        }
        if (preg_match('/^>\s+/m', $block)) {
            $quote = preg_replace('/^>\s?/m', '', $block);
            $html .= '<blockquote>' . nl2br(e(trim((string)$quote))) . '</blockquote>';
            continue;
        }
        if (preg_match('/^[-*]\s+/m', $block)) {
            $items = preg_split('/\r\n|\r|\n/', $block);
            $html .= '<ul>';
            foreach ($items as $item) {
                $item = trim(preg_replace('/^[-*]\s+/', '', trim($item)));
                if ($item !== '') $html .= '<li>' . e($item) . '</li>';
            }
            $html .= '</ul>';
            continue;
        }
        $html .= '<p>' . nl2br(e($block)) . '</p>';
    }
    return $html;
}


function format_blog_date($date): string {
    $raw = trim((string)$date);
    if ($raw === '') return '';
    $timestamp = strtotime($raw);
    if (!$timestamp) return $raw;
    return date('F j, Y', $timestamp);
}

function blog_share_url(array $post): string {
    return blog_post_url($post);
}

function get_blog_posts_filtered(array $filters = [], int $page = 1, int $perPage = 9): array {
    ensure_phase4_blog_schema();
    $pdo = try_db();
    $fallback = default_blog_posts();
    foreach ($fallback as &$item) {
        $item['category'] = $item['category'] ?? blog_default_category();
        $item['tags'] = $item['tags'] ?? 'sales meet, archive';
        $item['is_featured'] = $item['is_featured'] ?? (($item['id'] ?? 0) === 5 ? 1 : 0);
    }
    unset($item);
    if (!$pdo) {
        return ['posts' => $fallback, 'total' => count($fallback), 'pages' => 1, 'page' => 1, 'per_page' => $perPage];
    }
    $where = ['is_published = 1'];
    $params = [];
    $q = trim((string)($filters['q'] ?? ''));
    $category = trim((string)($filters['category'] ?? ''));
    $tag = trim((string)($filters['tag'] ?? ''));
    $year = trim((string)($filters['year'] ?? ''));
    if ($q !== '') {
        $like = '%' . $q . '%';
        $where[] = '(title_en LIKE ? OR title_bn LIKE ? OR excerpt_en LIKE ? OR excerpt_bn LIKE ? OR content_en LIKE ? OR content_bn LIKE ? OR tags LIKE ? OR category LIKE ?)';
        array_push($params, $like, $like, $like, $like, $like, $like, $like, $like);
    }
    if ($category !== '') { $where[] = 'category = ?'; $params[] = $category; }
    if ($tag !== '') { $where[] = 'tags LIKE ?'; $params[] = '%' . $tag . '%'; }
    if ($year !== '' && preg_match('/^\d{4}$/', $year)) { $where[] = 'YEAR(published_at) = ?'; $params[] = $year; }
    $whereSql = 'WHERE ' . implode(' AND ', $where);
    $page = max(1, $page);
    $perPage = max(3, min(24, $perPage));
    try {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $pages);
        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT * FROM blog_posts {$whereSql} ORDER BY is_featured DESC, COALESCE(published_at, created_at) DESC, sort_order ASC, id DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return ['posts' => $rows, 'total' => $total, 'pages' => $pages, 'page' => $page, 'per_page' => $perPage];
    } catch (Throwable $e) {
        return ['posts' => $fallback, 'total' => count($fallback), 'pages' => 1, 'page' => 1, 'per_page' => $perPage];
    }
}

function get_featured_blog_post(): ?array {
    ensure_phase4_blog_schema();
    $pdo = try_db();
    if ($pdo) {
        try {
            $stmt = $pdo->query('SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY is_featured DESC, COALESCE(published_at, created_at) DESC, sort_order ASC, id DESC LIMIT 1');
            $row = $stmt->fetch();
            if ($row) return $row;
        } catch (Throwable $e) {}
    }
    $posts = default_blog_posts();
    return $posts ? end($posts) : null;
}

function get_related_blog_posts(array $post, int $limit = 3): array {
    ensure_phase4_blog_schema();
    $pdo = try_db();
    $slug = (string)($post['slug'] ?? '');
    $category = trim((string)($post['category'] ?? ''));
    $tags = parse_blog_tags($post['tags'] ?? '');
    if ($pdo) {
        try {
            $where = ['is_published = 1', 'slug <> ?'];
            $params = [$slug];
            if ($category !== '') {
                $where[] = 'category = ?';
                $params[] = $category;
            } elseif ($tags) {
                $tagParts = [];
                foreach ($tags as $tag) { $tagParts[] = 'tags LIKE ?'; $params[] = '%' . $tag . '%'; }
                $where[] = '(' . implode(' OR ', $tagParts) . ')';
            }
            $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE ' . implode(' AND ', $where) . ' ORDER BY COALESCE(published_at, created_at) DESC, sort_order ASC, id DESC LIMIT ' . (int)$limit);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            if (count($rows) >= $limit) return $rows;
            $taken = array_column($rows, 'slug');
            $taken[] = $slug;
            $placeholders = implode(',', array_fill(0, count($taken), '?'));
            $more = $pdo->prepare("SELECT * FROM blog_posts WHERE is_published=1 AND slug NOT IN ({$placeholders}) ORDER BY COALESCE(published_at, created_at) DESC, sort_order ASC, id DESC LIMIT " . (int)($limit - count($rows)));
            $more->execute($taken);
            return array_slice(array_merge($rows, $more->fetchAll()), 0, $limit);
        } catch (Throwable $e) {}
    }
    return array_slice(array_values(array_filter(default_blog_posts(), static fn($item) => ($item['slug'] ?? '') !== $slug)), 0, $limit);
}


/* ==========================================================
   V36: Phase 5 SEO + Marketing helpers
   ========================================================== */
function phase5_setting_defaults(): array {
    return [
        'global_seo_title_suffix' => 'A TO Z & SIGNAL Mosquito Coil',
        'global_seo_keywords' => 'A TO Z mosquito coil, SIGNAL mosquito coil, mosquito coil Bangladesh, Nabiad Distribution Limited',
        'global_og_image' => 'assets/img/hero-atoz-pack.png',
        'organization_name' => 'Nabiad Distribution Limited',
        'organization_legal_name' => 'Nabiad Distribution Limited',
        'organization_logo' => 'assets/img/logo-atoz.webp',
        'organization_description' => 'Official business platform for A TO Z and SIGNAL mosquito coil products, distribution enquiries and company updates in Bangladesh.',
        'organization_phone' => '',
        'organization_email' => '',
        'organization_address' => '',
        'marketing_cta_en' => 'Become a Distributor',
        'marketing_cta_bn' => 'ডিস্ট্রিবিউটর হোন',
        'conversion_event_label' => 'Distributor Enquiry',
        'google_site_verification' => '',
    ];
}

function ensure_phase5_marketing_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = try_db();
    if (!$pdo) return;
    try {
        foreach (phase5_setting_defaults() as $key => $value) {
            $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_key = setting_key');
            $stmt->execute([$key, $value]);
        }
    } catch (Throwable $e) {}
}

function absolute_url(?string $path): string {
    $path = trim((string)$path);
    if ($path === '') return base_url();
    if (preg_match('~^https?://~i', $path)) return $path;
    return base_url($path);
}

function json_ld(array $data): string {
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>' . "
";
}

function organization_schema(array $settings): array {
    $name = trim((string)($settings['organization_name'] ?? '')) ?: ($settings['site_name'] ?? 'A TO Z & SIGNAL');
    $phone = trim((string)($settings['organization_phone'] ?? '')) ?: ($settings['contact_phone'] ?? '');
    $email = trim((string)($settings['organization_email'] ?? '')) ?: ($settings['email'] ?? '');
    $address = trim((string)($settings['organization_address'] ?? '')) ?: ($settings['office_address_en'] ?? '');
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $name,
        'legalName' => trim((string)($settings['organization_legal_name'] ?? '')) ?: $name,
        'url' => base_url(),
        'logo' => absolute_url($settings['organization_logo'] ?? 'assets/img/logo-atoz.webp'),
        'description' => trim((string)($settings['organization_description'] ?? '')) ?: ($settings['home_seo_description'] ?? ''),
    ];
    if ($phone !== '') $schema['telephone'] = $phone;
    if ($email !== '') $schema['email'] = $email;
    if ($address !== '') $schema['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $address, 'addressCountry' => 'BD'];
    if (!empty($settings['facebook'])) $schema['sameAs'] = [$settings['facebook']];
    return $schema;
}

function website_schema(array $settings): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $settings['site_name'] ?? 'A TO Z & SIGNAL',
        'url' => base_url(),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => page_url('blogs') . '?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

function webpage_schema(string $name, string $description, string $url): array {
    return [
        '@context' => 'https://schema.org', '@type' => 'WebPage',
        'name' => $name, 'description' => $description, 'url' => $url,
        'isPartOf' => ['@type' => 'WebSite', 'url' => base_url()],
    ];
}

function breadcrumb_schema(array $items): array {
    $list = [];
    foreach (array_values($items) as $idx => $item) {
        $list[] = ['@type' => 'ListItem', 'position' => $idx + 1, 'name' => $item['name'], 'item' => absolute_url($item['url'])];
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list];
}

function product_schema(array $product, array $settings): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product['name_en'] ?? '',
        'alternateName' => $product['name_bn'] ?? '',
        'brand' => ['@type' => 'Brand', 'name' => $product['brand'] ?? 'A TO Z & SIGNAL'],
        'category' => $product['category'] ?? 'Mosquito Coil',
        'image' => optimized_image_url($product['image'] ?? ''),
        'description' => $product['short_description_en'] ?? '',
        'url' => product_url($product),
        'manufacturer' => ['@type' => 'Organization', 'name' => $settings['organization_name'] ?? ($settings['site_name'] ?? 'A TO Z & SIGNAL')],
    ];
}

function product_faq_schema(array $faqs, array $product = []): array {
    $entities = [];
    foreach ($faqs as $item) {
        $q = trim((string)($item['q'] ?? ''));
        $a = trim((string)($item['a'] ?? ''));
        if ($q === '' || $a === '') continue;
        $entities[] = [
            '@type' => 'Question',
            'name' => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
        ];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'name' => trim((string)($product['name_en'] ?? 'Product FAQ')) . ' FAQ',
        'mainEntity' => $entities,
    ];
}

function product_comparison_rows(array $products): array {
    $rows = [];
    foreach ($products as $p) {
        $features = json_features($p['features'] ?? '');
        $rows[] = [
            'brand' => (string)($p['brand'] ?? ''),
            'name_en' => (string)($p['name_en'] ?? ''),
            'name_bn' => (string)($p['name_bn'] ?? ''),
            'slug' => (string)($p['slug'] ?? ''),
            'protection' => (string)($p['protection_hours'] ?? '—'),
            'pack_size' => trim((string)($p['pack_size'] ?? '')) ?: 'Trade pack',
            'smoke_type' => trim((string)($p['smoke_type'] ?? '')) ?: 'Standard comfort',
            'best_for' => trim((string)($p['room_size'] ?? '')) ?: trim((string)($p['category'] ?? 'Household use')),
            'highlight' => trim((string)($p['comparison_highlight'] ?? '')) ?: ($features[0] ?? trim((string)($p['badge'] ?? 'Premium protection'))),
            'accent_color' => (string)($p['accent_color'] ?? '#f5a623'),
        ];
    }
    return $rows;
}

function catalogue_target_url(array $settings, ?array $product = null): string {
    $productUrl = trim((string)($product['catalogue_url'] ?? ''));
    $url = $productUrl !== '' ? $productUrl : trim((string)($settings['catalogue_url'] ?? ''));
    $url = str_replace(["\r", "\n"], '', $url);
    return $url !== '' ? $url : '#';
}

function catalogue_download_url(array $settings, ?array $product = null, string $source = 'site'): string {
    $params = ['source' => $source];
    if ($product && !empty($product['slug'])) $params['product'] = (string)$product['slug'];
    return catalogue_route_url($params);
}

function ensure_catalogue_system_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS catalogue_downloads (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_slug VARCHAR(220) DEFAULT NULL,
            product_name VARCHAR(190) DEFAULT NULL,
            source VARCHAR(80) DEFAULT NULL,
            target_url VARCHAR(255) DEFAULT NULL,
            ip_address VARCHAR(64) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            downloaded_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_catalogue_downloaded (downloaded_at),
            INDEX idx_catalogue_product (product_slug),
            INDEX idx_catalogue_source (source)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {}
}

function record_catalogue_download(?array $product, string $source, string $targetUrl): void {
    ensure_catalogue_system_schema();
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $stmt = $pdo->prepare('INSERT INTO catalogue_downloads (product_slug, product_name, source, target_url, ip_address, user_agent, downloaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $product['slug'] ?? null,
            $product['name_en'] ?? null,
            substr($source, 0, 80),
            substr($targetUrl, 0, 255),
            client_ip(),
            security_user_agent(),
        ]);
    } catch (Throwable $e) {}
}

function article_schema(array $post, array $settings): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post['title_en'] ?? '',
        'description' => $post['excerpt_en'] ?? '',
        'image' => optimized_image_url($post['image'] ?? ''),
        'datePublished' => $post['published_at'] ?? '',
        'dateModified' => $post['updated_at'] ?? ($post['published_at'] ?? ''),
        'author' => ['@type' => 'Organization', 'name' => $settings['organization_name'] ?? ($settings['site_name'] ?? 'A TO Z & SIGNAL')],
        'publisher' => organization_schema($settings),
        'mainEntityOfPage' => blog_share_url($post),
    ];
}

/* ==========================================================
   V37: Phase 6 Security + Maintenance helpers
   ========================================================== */
function ensure_phase6_security_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_login_attempts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(80) DEFAULT NULL,
            ip_address VARCHAR(64) NOT NULL,
            attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            was_successful TINYINT(1) NOT NULL DEFAULT 0,
            user_agent VARCHAR(255) DEFAULT NULL,
            INDEX idx_login_attempt_ip_time (ip_address, attempted_at),
            INDEX idx_login_attempt_username_time (username, attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_activity_log (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            admin_id INT UNSIGNED DEFAULT NULL,
            admin_name VARCHAR(120) DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT NULL,
            ip_address VARCHAR(64) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_activity_admin_time (admin_id, created_at),
            INDEX idx_activity_action_time (action, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        // Shared-hosting safe: security features gracefully fall back to session-only behavior.
    }
}

function client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        $value = trim((string)($_SERVER[$key] ?? ''));
        if ($value === '') continue;
        if ($key === 'HTTP_X_FORWARDED_FOR') $value = trim(explode(',', $value)[0]);
        return substr($value, 0, 64);
    }
    return 'unknown';
}

function security_user_agent(): string {
    return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

function login_attempts_blocked(string $username = ''): bool {
    ensure_phase6_security_schema();
    $pdo = try_db();
    $ip = client_ip();
    if (!$pdo) {
        $bucket = $_SESSION['login_attempts'][$ip] ?? [];
        $recent = array_filter($bucket, static fn($ts) => $ts >= time() - 900);
        $_SESSION['login_attempts'][$ip] = $recent;
        return count($recent) >= 5;
    }
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_login_attempts WHERE ip_address = ? AND was_successful = 0 AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)");
        $stmt->execute([$ip]);
        if ((int)$stmt->fetchColumn() >= 5) return true;
        if ($username !== '') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_login_attempts WHERE username = ? AND was_successful = 0 AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)");
            $stmt->execute([$username]);
            return (int)$stmt->fetchColumn() >= 5;
        }
    } catch (Throwable $e) {}
    return false;
}

function record_login_attempt(string $username, bool $success): void {
    ensure_phase6_security_schema();
    $pdo = try_db();
    $ip = client_ip();
    if (!$pdo) {
        if (!$success) $_SESSION['login_attempts'][$ip][] = time();
        if ($success) unset($_SESSION['login_attempts'][$ip]);
        return;
    }
    try {
        $stmt = $pdo->prepare('INSERT INTO admin_login_attempts (username, ip_address, was_successful, user_agent) VALUES (?, ?, ?, ?)');
        $stmt->execute([substr($username,0,80), $ip, $success ? 1 : 0, security_user_agent()]);
        if ($success) {
            $pdo->prepare("DELETE FROM admin_login_attempts WHERE ip_address = ? AND was_successful = 0")->execute([$ip]);
        } else {
            $pdo->exec("DELETE FROM admin_login_attempts WHERE attempted_at < (NOW() - INTERVAL 7 DAY)");
        }
    } catch (Throwable $e) {}
}

function log_admin_action(string $action, string $details = ''): void {
    ensure_phase6_security_schema();
    $pdo = try_db();
    if (!$pdo) return;
    try {
        $stmt = $pdo->prepare('INSERT INTO admin_activity_log (admin_id, admin_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $_SESSION['admin_user_id'] ?? null,
            $_SESSION['admin_name'] ?? ($_SESSION['admin_username'] ?? null),
            substr($action, 0, 100),
            $details,
            client_ip(),
        ]);
    } catch (Throwable $e) {}
}

function enforce_admin_session_security(): void {
    $timeout = 45 * 60;
    $now = time();
    if (!empty($_SESSION['admin_last_seen']) && ($now - (int)$_SESSION['admin_last_seen']) > $timeout) {
        admin_logout();
        header('Location: login.php?timeout=1');
        exit;
    }
    $fingerprint = hash('sha256', client_ip() . '|' . security_user_agent());
    if (!empty($_SESSION['admin_fingerprint']) && !hash_equals($_SESSION['admin_fingerprint'], $fingerprint)) {
        admin_logout();
        header('Location: login.php?security=1');
        exit;
    }
    $_SESSION['admin_fingerprint'] = $fingerprint;
    $_SESSION['admin_last_seen'] = $now;
}

function password_is_strong(string $password): bool {
    return strlen($password) >= 10
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

function apply_security_headers(): void {
    if (headers_sent()) return;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $isAdmin = str_contains($script, '/admin/');
    $isApi = str_contains($script, '/api/');
    if ($isAdmin || $isApi) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    } else {
        header('Cache-Control: private, max-age=300, stale-while-revalidate=60');
    }
}

function maintenance_system_checks(): array {
    $checks = [];
    $checks[] = ['label' => 'PHP Version', 'value' => PHP_VERSION, 'ok' => version_compare(PHP_VERSION, '8.0.0', '>=')];
    $checks[] = ['label' => 'PDO MySQL', 'value' => extension_loaded('pdo_mysql') ? 'Available' : 'Missing', 'ok' => extension_loaded('pdo_mysql')];
    $checks[] = ['label' => 'File Uploads', 'value' => ini_get('file_uploads') ? 'Enabled' : 'Disabled', 'ok' => (bool)ini_get('file_uploads')];
    $checks[] = ['label' => 'GD Image Optimization', 'value' => extension_loaded('gd') ? 'Available' : 'Optional', 'ok' => true];
    $checks[] = ['label' => 'WEBP Output Support', 'value' => function_exists('imagewebp') ? 'Available' : 'Optional', 'ok' => true];
    $checks[] = ['label' => 'Pretty URL Rules', 'value' => is_file(__DIR__ . '/../.htaccess') ? 'Configured' : 'Check .htaccess', 'ok' => is_file(__DIR__ . '/../.htaccess')];
    foreach (['uploads/products','uploads/blog','uploads/team','uploads/admin','uploads/catalogues'] as $dir) {
        $path = __DIR__ . '/../' . $dir;
        $checks[] = ['label' => $dir, 'value' => is_dir($path) && is_writable($path) ? 'Writable' : 'Needs permission', 'ok' => is_dir($path) && is_writable($path)];
    }
    $checks[] = ['label' => 'Database', 'value' => try_db() ? 'Connected' : 'Not connected', 'ok' => (bool)try_db()];
    return $checks;
}

function build_operational_backup(): array {
    $pdo = db();
    $tables = ['site_settings','products','blog_posts','media_assets','content_blocks','distributor_enquiries','enquiry_notes','catalogue_downloads','site_team','company_timeline','admin_activity_log'];
    $backup = ['generated_at' => date('c'), 'site' => 'A TO Z & SIGNAL', 'tables' => []];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query('SELECT * FROM ' . $table . ' ORDER BY 1 DESC LIMIT 5000');
            $backup['tables'][$table] = $stmt->fetchAll();
        } catch (Throwable $e) {
            $backup['tables'][$table] = ['error' => $e->getMessage()];
        }
    }
    return $backup;
}

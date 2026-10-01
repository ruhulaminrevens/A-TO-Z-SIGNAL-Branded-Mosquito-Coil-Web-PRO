CREATE TABLE IF NOT EXISTS admin_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(120) DEFAULT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  password_updated_at DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS admin_login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) DEFAULT NULL,
  ip_address VARCHAR(64) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  was_successful TINYINT(1) NOT NULL DEFAULT 0,
  user_agent VARCHAR(255) DEFAULT NULL,
  INDEX idx_login_attempt_ip_time (ip_address, attempted_at),
  INDEX idx_login_attempt_username_time (username, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_activity_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED DEFAULT NULL,
  admin_name VARCHAR(120) DEFAULT NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT NULL,
  ip_address VARCHAR(64) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_admin_time (admin_id, created_at),
  INDEX idx_activity_action_time (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  brand ENUM('A TO Z','SIGNAL') NOT NULL,
  name_en VARCHAR(190) NOT NULL,
  name_bn VARCHAR(190) DEFAULT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  category VARCHAR(120) DEFAULT 'Mosquito Coil',
  protection_hours VARCHAR(40) DEFAULT NULL,
  pack_size VARCHAR(120) DEFAULT NULL,
  carton_size VARCHAR(120) DEFAULT NULL,
  smoke_type VARCHAR(120) DEFAULT NULL,
  fragrance VARCHAR(120) DEFAULT NULL,
  room_size VARCHAR(120) DEFAULT NULL,
  formula_type VARCHAR(160) DEFAULT NULL,
  burn_time VARCHAR(120) DEFAULT NULL,
  badge VARCHAR(80) DEFAULT NULL,
  short_description_en TEXT,
  short_description_bn TEXT,
  usage_instructions_en TEXT NULL,
  usage_instructions_bn TEXT NULL,
  safety_instructions_en TEXT NULL,
  safety_instructions_bn TEXT NULL,
  features JSON NULL,
  image VARCHAR(255) NOT NULL,
  gallery_json MEDIUMTEXT NULL,
  faq_json MEDIUMTEXT NULL,
  catalogue_url VARCHAR(255) DEFAULT NULL,
  comparison_highlight VARCHAR(180) DEFAULT NULL,
  accent_color VARCHAR(20) DEFAULT '#f5a623',
  seo_title VARCHAR(220) DEFAULT NULL,
  seo_description TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_products_active_sort (is_active, sort_order),
  INDEX idx_products_brand (brand),
  INDEX idx_products_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS distributor_enquiries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  phone_normalized VARCHAR(40) DEFAULT NULL,
  company_name VARCHAR(160) DEFAULT NULL,
  district VARCHAR(80) NOT NULL,
  business_address VARCHAR(220) DEFAULT NULL,
  business_type VARCHAR(80) NOT NULL,
  interested_brand VARCHAR(40) NOT NULL DEFAULT 'Both',
  product_id INT UNSIGNED NULL,
  product_slug VARCHAR(220) DEFAULT NULL,
  product_name VARCHAR(190) DEFAULT NULL,
  enquiry_type VARCHAR(60) NOT NULL DEFAULT 'Distributor',
  status VARCHAR(40) NOT NULL DEFAULT 'new',
  message TEXT NULL,
  lead_source VARCHAR(80) DEFAULT NULL,
  admin_note TEXT NULL,
  follow_up_at DATE NULL,
  handled_at DATETIME NULL,
  ip_address VARCHAR(64) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_enquiry_created (created_at),
  INDEX idx_enquiry_district (district),
  INDEX idx_enquiry_status (status),
  INDEX idx_enquiry_followup (follow_up_at),
  INDEX idx_enquiry_phone_normalized (phone_normalized),
  INDEX idx_enquiry_brand (interested_brand),
  INDEX idx_enquiry_product (product_slug),
  INDEX idx_enquiry_type (enquiry_type),
  INDEX idx_enquiry_source (lead_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enquiry_notes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS catalogue_downloads (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS media_assets (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_blocks (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS blog_posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title_en VARCHAR(220) NOT NULL,
  title_bn VARCHAR(220) DEFAULT NULL,
  slug VARCHAR(240) NOT NULL UNIQUE,
  category VARCHAR(120) DEFAULT 'Company Archive',
  tags VARCHAR(255) DEFAULT NULL,
  excerpt_en TEXT NULL,
  excerpt_bn TEXT NULL,
  content_en MEDIUMTEXT NULL,
  content_bn MEDIUMTEXT NULL,
  content_blocks_json TEXT NULL,
  editor_notes TEXT NULL,
  image VARCHAR(255) DEFAULT NULL,
  published_at DATE DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  seo_title VARCHAR(220) DEFAULT NULL,
  seo_description TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_blog_publish (is_published, published_at, sort_order),
  INDEX idx_blog_category (category),
  INDEX idx_blog_featured (is_featured, is_published, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin_users (username, name, password_hash, is_active) VALUES
('admin', 'Site Admin', '$2y$12$MoRc0Korav9ZoKbG8N/YjuVD3Zb3Z4tZuBDXiUwgbRCGWjrIf1Wf6', 1)
ON DUPLICATE KEY UPDATE username = username;

INSERT INTO products (brand, name_en, name_bn, slug, category, protection_hours, badge, short_description_en, short_description_bn, features, image, accent_color, sort_order, is_active) VALUES
('A TO Z','A TO Z Jumbo Coil','এ টু জেড জাম্বো কয়েল','atoz-jumbo-coil','Mosquito Coil','10H','Jumbo Size','Jumbo-size protection with steady burning for bigger rooms.','বড় ঘরের জন্য স্থিতিশীল বার্নিংসহ জাম্বো সাইজ সুরক্ষা.', JSON_ARRAY('10-hour protection','Large area coverage','Strong formula','Reliable burning'),'uploads/products/atoz-jumbo.webp','#f5a623',1,1),
('A TO Z','A TO Z Super Power Coil','এ টু জেড সুপার পাওয়ার কয়েল','atoz-super-power-coil','Mosquito Coil','12H','Best Seller','Strong daily mosquito defense with dependable overnight performance.','সারারাত নির্ভরযোগ্য পারফরম্যান্সসহ শক্তিশালী দৈনন্দিন মশা প্রতিরোধ.', JSON_ARRAY('12-hour protection','Strong knockdown','Mass killing formula','Premium quality'),'uploads/products/atoz-super-power.webp','#ffc533',2,1),
('A TO Z','A TO Z Turbo Coil','এ টু জেড টার্বো কয়েল','atoz-turbo-coil','Mosquito Coil','12H','Turbo Action','Fast-action coil experience with bold protection and ember glow.','দ্রুত অ্যাকশন, শক্তিশালী সুরক্ষা এবং এম্বার গ্লো সহ টার্বো কয়েল.', JSON_ARRAY('Turbo action','Rapid mosquito defense','Premium burn','Bold fragrance'),'uploads/products/atoz-turbo.webp','#ff6b35',3,1),
('SIGNAL','SIGNAL Plus Coil','সিগনাল প্লাস কয়েল','signal-plus-coil','Mosquito Coil','12H','Plus Formula','Balanced premium formula with strong protection and low-smoke comfort.','শক্তিশালী সুরক্ষা ও কম ধোঁয়ার আরামের ব্যালান্সড প্রিমিয়াম ফর্মুলা.', JSON_ARRAY('Low smoke tech','Pleasant scent','Family comfort','Advanced formula'),'uploads/products/signal-plus.webp','#2bbf5a',4,1),
('SIGNAL','SIGNAL Jama Coil','সিগনাল জামা কয়েল','signal-jama-coil','Mosquito Coil','12H','Daily Value','Everyday protection made for retailer-friendly demand and family trust.','রিটেইলার-ফ্রেন্ডলি ডিমান্ড ও পরিবারিক বিশ্বাসের জন্য দৈনন্দিন সুরক্ষা.', JSON_ARRAY('Daily protection','Stable burning','Retailer friendly','Trusted quality'),'uploads/products/signal-jama.webp','#34d66b',5,1),
('SIGNAL','SIGNAL Mega Coil','সিগনাল মেগা কয়েল','signal-mega-coil','Mosquito Coil','12H','Mega Shield','Wider protection shield with premium packaging and confident brand appeal.','প্রিমিয়াম প্যাকেজিং ও শক্তিশালী ব্র্যান্ড অ্যাপিলসহ ওয়াইড প্রোটেকশন শিল্ড.', JSON_ARRAY('Wide shield','Premium pack','Long lasting','Home protection'),'uploads/products/signal-mega.webp','#41e071',6,1),
('SIGNAL','SIGNAL Spider Micro Smoke','সিগনাল স্পাইডার মাইক্রো স্মোক','signal-spider-micro-smoke','Mosquito Coil','10H','Micro Smoke','Modern micro-smoke concept with powerful coverage and cleaner visuals.','শক্তিশালী কাভারেজ ও পরিষ্কার ভিজ্যুয়ালের জন্য মডার্ন মাইক্রো-স্মোক কনসেপ্ট.', JSON_ARRAY('Micro smoke tech','Deep corner reach','Modern pack','Powerful defense'),'uploads/products/signal-spider.webp','#ff4040',7,1)
ON DUPLICATE KEY UPDATE name_en = VALUES(name_en), image = VALUES(image), sort_order = VALUES(sort_order);

INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'A TO Z & SIGNAL Mosquito Coil'),
('contact_phone', '+880 1788-609529'),
('whatsapp_number', '8801788609529'),
('email', 'nabiadoffice@gmail.com'),
('facebook', 'https://www.facebook.com/NabiadAtoZ/'),
('office_address_en', 'Nabiad Distribution Limited, Dhaka, Bangladesh'),
('office_address_bn', 'নাবীয়াদ ডিস্ট্রিবিউশন লিমিটেড, ঢাকা, বাংলাদেশ'),
('catalogue_url', 'uploads/catalogues/catalogue-placeholder.txt'),
('catalogue_title_en', 'A TO Z & SIGNAL Product Catalogue'),
('catalogue_title_bn', 'এ টু জেড ও সিগনাল পণ্য ক্যাটালগ'),
('catalogue_version', '2026 Trade Edition'),
('catalogue_updated_at', '2026-06-19'),
('catalogue_note_en', 'Download the latest product catalogue for trade and distribution reference.'),
('catalogue_note_bn', 'ট্রেড ও ডিস্ট্রিবিউশন রেফারেন্সের জন্য সর্বশেষ পণ্য ক্যাটালগ ডাউনলোড করুন.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);


INSERT INTO content_blocks (block_key, placement, title_en, title_bn, body_en, body_bn, image, button_label_en, button_label_bn, button_url, sort_order, is_active) VALUES
('distribution-support','homepage','Distribution support built for retailers','রিটেইলারদের জন্য তৈরি ডিস্ট্রিবিউশন সাপোর্ট','Use this reusable block to highlight dealer communication, timely supply and market support without editing PHP files.','PHP ফাইল এডিট না করেই ডিলার যোগাযোগ, সময়মতো সরবরাহ ও মার্কেট সাপোর্ট তুলে ধরুন।','assets/img/hero-atoz-pack.webp','Send Distributor Enquiry','ডিস্ট্রিবিউটর ইনকোয়ারি দিন','#network',1,1),
('catalogue-cta','homepage','Keep product communication consistent','পণ্য যোগাযোগ রাখুন আরও একরকম ও প্রফেশনাল','Attach catalogue, product cards or announcement copy as reusable blocks and reuse them in homepage or blog stories.','ক্যাটালগ, প্রোডাক্ট কার্ড বা ঘোষণা কপি reusable block হিসেবে রেখে homepage বা blog story-তে ব্যবহার করুন।','assets/img/hero-signal-pack.webp','Download Catalogue','ক্যাটালগ ডাউনলোড','catalogue',2,1)
ON DUPLICATE KEY UPDATE title_en = VALUES(title_en), placement = VALUES(placement), sort_order = VALUES(sort_order);


INSERT INTO blog_posts (title_en, title_bn, slug, category, tags, excerpt_en, excerpt_bn, content_en, content_bn, content_blocks_json, editor_notes, image, published_at, sort_order, is_featured, is_published) VALUES
('1st Sales Meet 2017','১ম সেলস মিট ২০১৭','sales-meet-2017','Sales Meet','sales meet, archive, team','A milestone gathering that strengthened the early distribution journey of Nabiad Distribution Limited.','নাবীয়াদ ডিস্ট্রিবিউশন লিমিটেডের প্রাথমিক ডিস্ট্রিবিউশন যাত্রাকে আরও শক্তিশালী করা একটি গুরুত্বপূর্ণ সেলস মিট।','The 1st Sales Meet 2017 marked an important step in building a stronger sales culture, product confidence and distribution teamwork.','১ম সেলস মিট ২০১৭ সেলস কালচার, পণ্যের প্রতি আত্মবিশ্বাস এবং ডিস্ট্রিবিউশন টিমওয়ার্ককে আরও শক্তিশালী করার একটি গুরুত্বপূর্ণ ধাপ ছিল।',NULL,NULL,'uploads/blog/sales-meet-2017.jpeg','2017-09-19',1,0,1),
('Launching Ceremony 2018','লঞ্চিং সিরেমনি ২০১৮','launching-ceremony-2018','Product Launch','launch, product, archive','Product launch event highlighting brand growth and channel development.','ব্র্যান্ড গ্রোথ ও চ্যানেল ডেভেলপমেন্টকে কেন্দ্র করে আয়োজিত পণ্য লঞ্চিং ইভেন্ট।','The 2018 launching ceremony brought together sales leaders and business partners for stronger product visibility and retail momentum.','২০১৮ সালের লঞ্চিং সিরেমনি সেলস লিডার ও বিজনেস পার্টনারদের একত্রিত করে পণ্যের দৃশ্যমানতা ও রিটেইল মোমেন্টামকে শক্তিশালী করেছে।',NULL,NULL,'uploads/blog/launching-ceremony-2018.jpeg','2018-04-28',2,0,1),
('Annual Sales Meet 2021','বার্ষিক সেলস মিট ২০২১','annual-sales-meet-2021','Sales Meet','sales meet, planning, team','Annual sales meet focused on alignment, team spirit and market execution.','সমন্বয়, টিম স্পিরিট এবং বাজার বাস্তবায়নকে কেন্দ্র করে বার্ষিক সেলস মিট।','Annual Sales Meet 2021 focused on distribution discipline, sales planning and stronger market execution across key territories.','বার্ষিক সেলস মিট ২০২১ মূল টেরিটরিগুলোতে ডিস্ট্রিবিউশন শৃঙ্খলা, সেলস প্ল্যানিং এবং বাজার বাস্তবায়নকে শক্তিশালী করার উপর গুরুত্ব দিয়েছে।',NULL,NULL,'uploads/blog/annual-sales-meet-2021.jpeg','2021-02-06',3,0,1),
('SIGNAL Spider Launch 2024','সিগনাল স্পাইডার লঞ্চ ২০২৪','signal-spider-launch-2024','Product Launch','SIGNAL, launch, spider, micro smoke','A premium launch moment for SIGNAL Spider Micro Smoke, introducing a modern product story.','SIGNAL Spider Micro Smoke-এর আধুনিক পণ্য গল্পকে সামনে আনা একটি প্রিমিয়াম লঞ্চ মুহূর্ত।','SIGNAL Spider Micro Smoke launch activities highlighted modern product appeal, channel confidence and a refreshed market presence.','SIGNAL Spider Micro Smoke লঞ্চ কার্যক্রম আধুনিক পণ্য উপস্থাপনা, চ্যানেল আস্থা এবং নতুন বাজার উপস্থিতিকে সামনে এনেছে।',NULL,NULL,'uploads/blog/sales-meet-2024-launch.jpg','2024-01-01',4,0,1),
('6th Sales Meet 2025','৬ষ্ঠ সেলস মিট ২০২৫','sales-meet-2025','Sales Meet','sales meet, 2025, distribution','A major team event reflecting the distribution strength and future growth focus of Nabiad Distribution Limited.','নাবীয়াদ ডিস্ট্রিবিউশন লিমিটেডের ডিস্ট্রিবিউশন শক্তি ও ভবিষ্যৎ গ্রোথ ফোকাস তুলে ধরা একটি গুরুত্বপূর্ণ টিম ইভেন্ট।','The 6th Sales Meet 2025 represents a stronger national team spirit, channel development and continued commitment to market growth.','৬ষ্ঠ সেলস মিট ২০২৫ জাতীয় টিম স্পিরিট, চ্যানেল ডেভেলপমেন্ট এবং বাজার প্রবৃদ্ধির প্রতি ধারাবাহিক অঙ্গীকারকে তুলে ধরে।',NULL,NULL,'uploads/blog/sales-meet-2025-stage.jpg','2025-01-01',5,1,1)
ON DUPLICATE KEY UPDATE title_en = VALUES(title_en), image = VALUES(image), category = VALUES(category), tags = VALUES(tags), is_featured = VALUES(is_featured), updated_at = NOW();

-- V31 Phase 2 CMS tables
CREATE TABLE IF NOT EXISTS site_team (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS company_timeline (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('hero_pill_en','Trusted Protection for Generations'),
('hero_pill_bn','প্রজন্মের পর প্রজন্ম বিশ্বস্ত সুরক্ষা'),
('hero_title_line1_en','No more mosquito bites,'),
('hero_title_line1_bn','মশার যন্ত্রণা আর নয়,'),
('hero_title_line2_en','Peaceful every night.'),
('hero_title_line2_bn','রাত হোক শান্তিময়।'),
('home_seo_title','Reliable Mosquito Protection for Bangladesh'),
('home_seo_description','Official A TO Z and SIGNAL mosquito coil website featuring premium products, protection technology and nationwide distributor support across Bangladesh.'),
('footer_description_en','A TO Z and SIGNAL provide dependable mosquito protection solutions trusted by households, retailers and distribution partners across Bangladesh.'),
('footer_description_bn','A TO Z এবং SIGNAL বাংলাদেশজুড়ে পরিবার, রিটেইলার ও ডিস্ট্রিবিউশন পার্টনারদের জন্য নির্ভরযোগ্য মশা সুরক্ষা সমাধান প্রদান করে।')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- V36 Phase 5 SEO and marketing settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('global_seo_title_suffix','A TO Z & SIGNAL Mosquito Coil'),
('global_seo_keywords','A TO Z mosquito coil, SIGNAL mosquito coil, mosquito coil Bangladesh, Nabiad Distribution Limited'),
('global_og_image','assets/img/hero-atoz-pack.png'),
('organization_name','Nabiad Distribution Limited'),
('organization_legal_name','Nabiad Distribution Limited'),
('organization_logo','assets/img/logo-atoz.webp'),
('organization_description','Official business platform for A TO Z and SIGNAL mosquito coil products, distribution enquiries and company updates in Bangladesh.'),
('organization_phone','+880 1788-609529'),
('organization_email','nabiadoffice@gmail.com'),
('organization_address','Nabiad Distribution Limited, Dhaka, Bangladesh'),
('marketing_cta_en','Become a Distributor'),
('marketing_cta_bn','ডিস্ট্রিবিউটর হোন'),
('conversion_event_label','Distributor Enquiry'),
('google_site_verification','')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

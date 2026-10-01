# A TO Z & SIGNAL PHP Website

## Purpose, Current Version, Full Project Code Map & Remaining Work Report

**Audit baseline:** `atoz-signal-php-premium-v44-technical-seo-core-web-vitals.zip`  
**Current code version:** v44 — Technical SEO & Core Web Vitals  
**Asset cache version:** 2.44.0  
**Report date:** 1 October 2026  
**Project type:** Shared-hosting friendly PHP/MySQL business website, mini CMS and lead CRM  
**Target public domain assumed by fallback files:** `https://atoz.nabiad.com/`

> This report is based on the complete v44 source package. “Done” means the feature exists in the code and passed the stated static check. It does not mean the production server or Google account has been verified unless explicitly stated.

---

## 1. Project Purpose

The project is the official digital business platform for **A TO Z** and **SIGNAL** mosquito coil brands. Its purpose is to:

- present brands, products, specifications, safety instructions and catalogues;
- collect distributor and product enquiries throughout Bangladesh;
- manage leads, follow-ups, notes, duplicate contacts and conversion stages;
- publish company news, sales meets, launches and archive content;
- manage media, reusable content blocks, team members and company timeline;
- provide SEO-ready product/blog pages with clean URLs and structured data;
- run on conventional PHP/MySQL shared hosting without requiring Node.js.

The project is therefore more than a landing page. It currently operates as a **business website + product CMS + content CMS + media library + mini CRM + SEO/maintenance control panel**.

---

## 2. Current and Last Version

| Item | Verified status |
|---|---|
| Latest available source package | v44 ZIP |
| Application label | `v44 Technical SEO & Core Web Vitals` |
| Asset version | `2.44.0` |
| Previous version | v43 — Media & Content System |
| Total files | 134 |
| PHP files | 42 |
| Database tables in fresh schema | 13 |
| Image files | 70 |
| Original PNG/JPG files with WebP pair | 35 of 35 |
| ZIP integrity | Passed |
| JavaScript syntax | Passed for public/admin scripts |
| PHP CLI lint | Not run: PHP CLI unavailable in the audit workspace |
| Package SHA-256 | `4117e176ce902467a53083adfc24408f5d207603067cd0e1aa125ac16a4be0e2` |

### Important inconsistency

`config/app.php` and `VERSION_HISTORY.md` identify the package as v44, but the first heading and current-package statement in `README.md` still say v43. The README is therefore outdated and must be corrected before the next release/deployment handover.

---

## 3. Full Project Code Map

### Public website

| File / route | Purpose |
|---|---|
| `index.php` | Homepage, products, comparison, technology, brand story, district coverage, enquiry form, office/contact sections |
| `product-details.php` / `/product/{slug}` | Product details, gallery, specifications, usage, safety, FAQ, enquiry and related products |
| `blog.php` / `/blogs` | Searchable/filterable blog and company archive |
| `blog-details.php` / `/blog/{slug}` | Article details, sharing, rich content blocks and related posts |
| `about-us.php` / `/about` | Company introduction, brands, leadership and timeline |
| `privacy-policy.php` | Privacy policy |
| `terms-conditions.php` | Terms and conditions |
| `catalogue-download.php` / `/catalogue` | Catalogue redirect and download tracking |
| `sitemap.php` / `/sitemap.xml` | Dynamic XML sitemap |
| `robots.php` / `/robots.txt` | Dynamic robots directives |
| `404.php` | Branded not-found page and recovery links |
| `api/enquiry.php` | Public distributor/product enquiry endpoint |

### Admin and operations

| File / area | Purpose |
|---|---|
| `admin/index.php` | Dashboard and operational overview |
| `admin/products.php`, `product-form.php` | Product CRUD, ordering, specifications, gallery, FAQ and SEO |
| `admin/enquiries.php` | CRM pipeline, search, follow-ups, notes, duplicates, status and CSV export |
| `admin/blogs.php`, `blog-form.php` | Blog/archive publishing and SEO |
| `admin/media.php` | Media upload, WebP optimization, indexing and metadata |
| `admin/content-blocks.php` | Reusable homepage/blog content blocks |
| `admin/content.php` | Homepage, About, footer, contact and catalogue content |
| `admin/team.php`, `team-form.php` | Leadership management |
| `admin/timeline.php`, `timeline-form.php` | Company timeline management |
| `admin/seo.php` | SEO, OG, schema, Search Console token, analytics and backlink guidance |
| `admin/maintenance.php` | System health, backups, log cleanup and activity review |
| `admin/profile.php` | Admin identity, password and profile image |

### Core, database and assets

| Path | Purpose |
|---|---|
| `includes/functions.php` | Main application, CMS, CRM, SEO, schema, upload and security helpers |
| `includes/site-ui.php` | Shared public header/footer, metadata, navigation and scripts |
| `includes/auth.php` | Authentication helpers |
| `includes/db.php` | Database connection/bootstrap |
| `config/app.php` | Site/application configuration |
| `config/database.php` | MySQL credentials |
| `database/schema.sql` | Fresh-install database schema and initial content |
| `.htaccess` | Pretty URLs, redirects, HTTPS, headers, compression and caching |
| `assets/css/style.css` | Public UI and responsive styling |
| `assets/css/admin.css` | Admin UI |
| `assets/js/main.js` | Public interaction and animation logic |
| `assets/js/admin.js` | Admin interaction logic |
| `uploads/` | Products, blog, team, admin, SEO, media and catalogue assets |
| `VERSION_HISTORY.md` | Single authoritative version/update log |

### Database tables

`admin_users`, `admin_login_attempts`, `admin_activity_log`, `products`, `distributor_enquiries`, `enquiry_notes`, `catalogue_downloads`, `media_assets`, `content_blocks`, `blog_posts`, `site_settings`, `site_team`, `company_timeline`.

---

## 4. Current Status Summary

| Status | Meaning | Count |
|---|---|---:|
| Done | Present and statically verified in v44 source | 26 |
| Still Pending | Missing, incomplete or still using placeholder/outdated data | 10 |
| Needs Re-test | Code exists, but live hosting/database/account verification is required | 13 |
| No Longer Needed | Superseded recommendation or duplicate work | 8 |

---

## 5. Done Checklist

- [x] **Responsive public website:** desktop and mobile CSS/JS foundation exists.
- [x] **Product CMS:** create/edit, ordering, visibility, specifications, gallery, FAQ, safety/use and custom SEO fields exist.
- [x] **Product detail pages:** clean product routes, related items and product enquiry actions exist.
- [x] **Blog/archive CMS:** categories, tags, featured status, search/filter and rich content rendering exist.
- [x] **Media Library:** uploads, asset indexing, metadata and WebP generation foundation exist.
- [x] **Reusable Content Blocks:** homepage rendering and blog shortcodes exist.
- [x] **Lead CRM:** pipeline statuses, notes history, follow-up dates, duplicate phone detection, analytics and CSV export exist.
- [x] **Team and Timeline CMS:** admin management and public rendering exist.
- [x] **Admin profile/security foundation:** strong-password check, session timeout/fingerprint, rate-limited login and activity logs exist.
- [x] **Operational backup:** JSON export includes core business tables.
- [x] **Dynamic sitemap:** products, blogs and public pages are included.
- [x] **Dynamic robots output:** private/internal folders and tracking-query patterns are controlled.
- [x] **Canonical URLs:** shared metadata helpers and clean canonical routes exist.
- [x] **Meta titles and descriptions:** homepage, shared pages, products and blogs have automatic/custom metadata.
- [x] **One H1 per audited public template:** all eight audited public templates contain one H1.
- [x] **Header hierarchy foundation:** public templates use one H1 followed by section-level H2/H3 headings.
- [x] **Image alt coverage:** public image tags include alt attributes; decorative images use empty alt where appropriate.
- [x] **Structured data:** Organization, WebSite, WebPage, Product, Article, FAQ and Breadcrumb JSON-LD helpers exist.
- [x] **Internal linking:** navigation, footer, product, blog, related-content and 404 recovery links exist.
- [x] **Clean URL routes:** product, blog, About, catalogue, sitemap and robots routes exist.
- [x] **Legacy URL redirects:** old PHP page URLs redirect to clean public URLs through `.htaccess`.
- [x] **HTTPS enforcement code:** production redirect and HSTS directives exist in `.htaccess`.
- [x] **OG/social metadata:** OG image, dimensions, locale and Twitter card metadata exist.
- [x] **WebP coverage:** all 35 PNG/JPG source images have a matching WebP version.
- [x] **Core Web Vitals foundation:** hero preload, image dimensions/fetch priority, cache headers, compression and reduced-motion support exist.
- [x] **Accessibility foundation:** skip link, `main-content` targets, labels and reduced-motion handling exist.

---

## 6. Still Pending Checklist

- [ ] **Update `README.md` from v43 to v44.** It currently reports the wrong current package/version.
- [ ] **Set the production `base_url`.** `config/app.php` is blank; use `https://atoz.nabiad.com` to prevent host/proxy-dependent canonical URLs.
- [ ] **Replace the catalogue placeholder.** Both config/schema still point to `uploads/catalogues/catalogue-placeholder.txt`; upload a real PDF and save its path.
- [ ] **Add a dedicated 1200×630 OG image.** Current default is a product hero asset, not a verified social-preview composition.
- [ ] **Complete Search Console verification.** The code field exists, but the verification token is empty.
- [ ] **Submit the live sitemap in Search Console.** Code creation is complete; account submission is still pending.
- [ ] **Convert destructive product/blog/team/timeline actions from GET to POST.** CSRF tokens exist, but delete/toggle/feature actions still use query-string requests.
- [ ] **Add media usage detection and trash/restore.** Media deletion is POST-protected, but reference detection and reversible deletion are not implemented.
- [ ] **Reduce package/storage duplication.** WebP is served preferentially, but original and WebP copies keep the image payload around 22.8 MB; define an archival policy before deleting originals.
- [ ] **Add responsive `srcset`/`sizes`.** WebP and dimensions exist, but device-specific image variants are not yet generated/served.

---

## 7. Needs Re-test Checklist

- [ ] **PHP syntax lint:** run against the deployed PHP version because PHP CLI was unavailable during this audit.
- [ ] **Fresh database installation:** import `database/schema.sql` into an empty database and verify all 13 tables/default rows.
- [ ] **Existing database upgrade:** open required admin pages and confirm every compatibility migration completes without restricted `ALTER/CREATE` permissions.
- [ ] **Admin login security:** test five failed attempts, 15-minute lockout, successful login, 45-minute timeout and session fingerprint behavior.
- [ ] **Product/blog/team/timeline CRUD:** create, edit, reorder, hide/show and delete test records on staging.
- [ ] **Public enquiry flow:** submit valid/invalid/duplicate leads and verify database save, UI response, admin appearance and rate-limiting behavior.
- [ ] **Catalogue download tracking:** verify the real PDF opens and a tracking row is written.
- [ ] **Media/WebP processing:** confirm the Hostinger PHP build has GD + WebP support and that file permissions allow uploads/variants.
- [ ] **Pretty URLs and redirects:** test Apache `mod_rewrite`, legacy redirects, nested product/blog URLs and 404 handling.
- [ ] **HTTPS/HSTS:** verify the domain, proxy/CDN and certificate before relying on HSTS; confirm no redirect loop.
- [ ] **Live metadata/schema:** inspect homepage, product and blog URLs with Google Rich Results Test and social debuggers.
- [ ] **Mobile responsiveness:** test real devices at 320, 360, 390, 768 and desktop widths, including menus, tables, modal, forms and Bangla copy.
- [ ] **Core Web Vitals:** run PageSpeed/Lighthouse on the deployed domain and record LCP, INP and CLS; code-level optimization alone is not a live performance result.

---

## 8. No Longer Needed Checklist

- [x] **Create a sitemap from scratch:** no longer needed; dynamic sitemap already exists.
- [x] **Create robots.txt from scratch:** no longer needed; dynamic and fallback versions already exist.
- [x] **Add canonical tags from scratch:** no longer needed; shared canonical handling exists.
- [x] **Add basic product/blog SEO fields:** no longer needed; both content types already support custom title/description.
- [x] **Add basic schema markup:** no longer needed; multiple JSON-LD types already exist.
- [x] **Add a separate standalone media uploader:** no longer needed; Media Library covers it.
- [x] **Add a separate spreadsheet-based lead tracker:** no longer needed for normal website leads; the built-in CRM is the primary system.
- [x] **Create separate version markdown files:** explicitly prohibited; all version notes must remain in `VERSION_HISTORY.md` only.

---

## 9. Priority-Based Remaining Work

### P0 — Before deployment or replacement of the live site

1. Correct README version/current-package text.
2. Set and confirm the production base URL.
3. Configure the real database credentials outside any public/shared copy.
4. Upload a real catalogue PDF and save its path.
5. Run PHP lint and fresh/existing database migration tests.
6. Take a full database/files backup of the current live site.

### P1 — Immediately after deployment

1. Test HTTPS and pretty URL redirects without a loop.
2. Smoke-test homepage, About, blogs, every product page, enquiry form and admin login.
3. Upload the proper 1200×630 OG image.
4. Add the Search Console verification token, verify ownership and submit `/sitemap.xml`.
5. Run Rich Results, PageSpeed Insights and mobile-device checks.

### P2 — Next safe development release

1. Convert remaining GET mutations to POST + CSRF.
2. Add media usage detection, trash and restore.
3. Add responsive image variants with `srcset` and `sizes`.
4. Add enquiry IP/phone throttling tests and clearer admin warnings.
5. Add automated smoke-test scripts for routes, metadata and internal links.

### P3 — Business growth improvements

1. Lead assignment by sales person/ASM.
2. Dynamic district coverage and distributor finder.
3. Scheduled publishing and content revisions.
4. Campaign landing-page/placement manager.
5. District/product/source conversion reporting.

---

## 10. Backlink Strategy — Current Action Plan

- Complete consistent business profiles using the same organization name, phone, address and canonical homepage.
- Request relevant links from manufacturers, brand principals, verified distributors and retail partners.
- Publish useful articles about safe mosquito-coil use, mosquito awareness, distributor support and product comparisons.
- Seek relevant Bangladesh trade-directory, local-news and event coverage links.
- Point each backlink to the most relevant product/article page, not always the homepage.
- Avoid paid link farms, automated comments, unrelated directories and repetitive exact-match anchors.

Backlink work cannot be marked Done from source code. It is an ongoing off-site activity and should be reviewed monthly through Search Console.

---

## 11. Deployment Acceptance Criteria

Deployment should be accepted only when all items below pass:

- [ ] Backup completed and recoverable.
- [ ] PHP lint passes for every PHP file.
- [ ] Database connection and required tables confirmed.
- [ ] Admin login and lockout behavior confirmed.
- [ ] Homepage, About, blog archive, sample article and every active product load without PHP warnings.
- [ ] Enquiry reaches the CRM and duplicate/rate-limit behavior is checked.
- [ ] Real catalogue downloads successfully.
- [ ] HTTP redirects once to HTTPS; no loop.
- [ ] Clean URLs, sitemap.xml, robots.txt and 404 return correct responses.
- [ ] Canonical, title, description, OG image and JSON-LD are correct on representative URLs.
- [ ] Mobile menu, language/theme controls, forms, modal and tables work on real mobile widths.
- [ ] Search Console ownership verified and sitemap submitted.
- [ ] PageSpeed/Lighthouse baseline saved for future comparison.

---

## 12. Final Assessment

v44 is a substantial and usable shared-hosting business platform. The major public website, CMS, CRM, media, SEO and maintenance foundations are present. The project should **not** be rebuilt from scratch.

The real remaining work is concentrated in four areas:

1. deployment configuration and live verification;
2. README/catalogue/OG/Search Console completion;
3. POST-only admin mutations and safer media deletion;
4. measured performance/mobile/database testing on the production hosting environment.

**Recommended baseline going forward:** `atoz-signal-php-premium-v44-technical-seo-core-web-vitals.zip`. Create the next version only after the selected pending work is implemented and re-tested. Keep all update notes inside `VERSION_HISTORY.md`.


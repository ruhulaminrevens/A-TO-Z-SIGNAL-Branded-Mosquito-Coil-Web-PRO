# A TO Z & SIGNAL PHP Website — v44

Current package: **v44 Technical SEO & Core Web Vitals**

This is a shared-hosting friendly PHP/MySQL business platform for A TO Z and SIGNAL mosquito coil brands. It includes the public brand website, clean product/blog routes, product and content CMS, Media Library, reusable Content Blocks, distributor enquiry CRM, SEO/schema controls, backups and a security/maintenance center.

## Current Baseline

- Application: `v44 Technical SEO & Core Web Vitals`
- Asset cache: `2.44.0`
- Previous completed release: `v43 Media & Content System`
- Version history: [`VERSION_HISTORY.md`](VERSION_HISTORY.md)
- Project status report: [`docs/A_TO_Z_SIGNAL_Project_Status_Report_v44.md`](docs/A_TO_Z_SIGNAL_Project_Status_Report_v44.md)
- Interactive HTML report: [`docs/A_TO_Z_SIGNAL_Project_Status_Report_v44.html`](docs/A_TO_Z_SIGNAL_Project_Status_Report_v44.html)

## v44 Highlights

- Dynamic XML sitemap and robots output
- Canonical URLs, clean routes and legacy redirects
- Meta title/description controls and Open Graph/Twitter metadata
- Organization, Website, WebPage, Product, Article, FAQ and Breadcrumb schema
- One-H1 public templates, image alt text and accessibility improvements
- WebP preference, image dimensions, preload, caching and compression foundation
- HTTPS redirect and HSTS directives for production hosting
- Search Console verification-token control in Admin → SEO & Marketing

## Admin Pages

- `admin/index.php` — Dashboard
- `admin/products.php` — Product management and readiness overview
- `admin/product-form.php` — Product specs, gallery, FAQ, safety and SEO
- `admin/enquiries.php` — Lead CRM, follow-ups, notes, duplicates and analytics
- `admin/content.php` — Content CMS
- `admin/media.php` — Media Library and WebP optimization
- `admin/content-blocks.php` — Reusable content blocks
- `admin/team.php` — Leadership/team management
- `admin/timeline.php` — Company timeline/archive management
- `admin/blogs.php`, `admin/blog-form.php` — Blog/archive management
- `admin/seo.php` — SEO, schema, social preview and tracking controls
- `admin/maintenance.php` — Security, logs, backup and health checks
- `admin/profile.php` — Admin profile/password/photo

## Public SEO URLs

- `/product/{slug}` — Product details
- `/blog/{slug}` — Blog details
- `/blogs` — Blog archive
- `/about` — About Us
- `/catalogue` — Catalogue redirect/tracking
- `/sitemap.xml` — Dynamic sitemap
- `/robots.txt` — Dynamic robots output

## Deployment

1. Upload the repository files to the shared-hosting web root or subdomain folder.
2. Import `database/schema.sql` for a fresh install. For an existing database, take a backup first and then open the key admin modules once so compatibility helpers can apply required additions.
3. Update `config/database.php` on the server with the real database credentials. Never commit live credentials to this public repository.
4. Set the production `base_url` in `config/app.php`.
5. Replace `uploads/catalogues/catalogue-placeholder.txt` with the real catalogue PDF and update the configured path.
6. Login to `/admin/login.php`, immediately change the seeded admin password and review **Security & Maintenance**.
7. Review **SEO & Marketing**, upload the final 1200×630 OG image, add the Search Console token and submit `/sitemap.xml` after deployment.

## Security Notice

- The repository contains placeholder database credentials only.
- Keep production credentials outside Git history.
- Rotate any credential that has previously been shared inside a source archive.
- Run the pending and re-test checklist in the project status report before launch.

## Validation & Version Rules

- Keep all future update notes only inside `VERSION_HISTORY.md`.
- Do not create separate version markdown files.
- Run PHP syntax lint using the production PHP version before deployment.
- v44 JavaScript syntax, static SEO checks and source ZIP integrity passed; production database, HTTPS, form and Core Web Vitals still require live-host testing.

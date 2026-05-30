# MiniLicensePlates.com — Master Build Prompt

Copy everything inside the fenced block below and paste it as your first message to an AI coding assistant when building this site from scratch.

---

```
Build MiniLicensePlates.com from scratch exactly as specified below. This is a Laravel 12 + MySQL + Blade application for collectors of miniature license plate premiums (cereal/gum/candy toys). Implement all features, migrations, routes, views, CSS, admin CMS, member tools, and deploy scaffolding described here. Do not use SQLite, Vite, or React. Do not commit public/plates/ or mlp_code/ to git.

================================================================================
1. PROJECT MISSION
================================================================================

Site: MiniLicensePlates.com (minilicenseplates.com)
Audience: Collectors of miniature license plates issued as product premiums.

Goals:
- Authoritative visual reference and pricing guide for 3,150+ catalog listings
- Searchable database (not filesystem scan) with plate photos on disk
- Member accounts to track personal collections and print PDF checklists
- Admin CMS for catalog, content, users, and homepage hero
- History timeline and articles for SEO and engagement
- Newsletter signup; Google AdSense-ready ad slots; future on-site store mentioned in copy
- Tone: warm, authoritative, collector-focused

Round displayed catalog count to nearest 50 on marketing copy (e.g. 3,118 → 3,150+).

================================================================================
2. TECH STACK & CONSTRAINTS
================================================================================

- Laravel 12, PHP 8.2+, MySQL only (database name: minilicenseplates locally)
- Blade views ONLY in resources/views/ (no raw .php view files)
- Single CSS file: public/main.css (no Vite/build step)
- Sessions, cache, queue: database drivers
- Local dev: XAMPP MySQL + php artisan serve at http://localhost:8000
- .env: DB_USERNAME=root, DB_PASSWORD= empty for local XAMPP
- Git ignore: public/plates/, mlp_code/, .env
- Use GitHub Desktop for commits (document this; do not rely on Cursor git)

Production hosting layout:
- Application: /home/minilp/laravel/
- Public web root: /home/minilp/public_html/ (sibling folder, NOT laravel/public)
- public_html/index.php bootstraps ../laravel
- WebPublicPath helper: if sibling public_html exists with index.php, use it for uploads and image paths; else use public_path() for local dev
- Deploy: PowerShell script Build-DeployRelease.ps1 packages laravel/ + public_html/ for FileZilla upload; docs/DEPLOY_PLAIN_ENGLISH.md for step-by-step owner instructions

================================================================================
3. VISUAL DESIGN SYSTEM (public/main.css)
================================================================================

CSS variable --site-max-width: 1150px for white content column.

Page:
- Tiled background: public/background_img.jpg (repeat)
- White content box centered, max-width 1150px, ~4px margin at sides/bottom so background shows

Header:
- Split banners: public/header_banner_left.png + header_banner_right.png
- Flex layout, left/right anchored, max-height 175px, object-fit contain
- Header width matches content column

Navigation:
- Background #4079a5
- Link text #fab95b, Open Sans bold (or closest Google Font), ~20% smaller than initial draft so all links fit one line
- White border around nav block, square corners (no border-radius)
- Items: HOME | GALLERY | SEARCH | HISTORY | ARTICLES | MORE (dropdown) | MY COLLECTION or SIGN IN | SHOP (external eBay link)
- MORE dropdown: hover-only (not always visible); About, Contribute; left-aligned links, 8px padding
- HISTORY and ARTICLES are top-level nav links, NOT inside MORE

Logged-in session bar (below nav, inside nav block):
- Profile thumbnail + "Logged in as {username}" (links to profile edit only — NOT to collection)
- If is_admin: "admin" text link to /admin
- "Log out" button to the right
- Do NOT show "My Collection" link here (nav already has MY COLLECTION)

Footer:
- Based on footer_base_img.png with alpca_logo.png, avatar.png
- Placeholder link columns left/right for future pages
- Copyright bar in light shade matching footer art
- Newsletter: email input + submit; validate email; store in newsletter_subscribers with subscribed_at timestamp
- Footer sits inside white content box with small margin matching sides

Home page sections:
- Hero rotator flush to top (no gap above): admin-managed slides from DB, JS rotator (public/js/home-hero.js), configurable interval
- Welcome: "MiniLicensePlates.com" semi-bold, 10% larger
- H1 + lead paragraph about visual reference to mini plates from 1930s–today
- Stat block: "{count}+ subjects in the catalog"
- Mission section with blue_back_composite_img_sm.jpg on right
- Copy mentions: history section, printable checklists, upcoming store on this website
- Three feature cards (Set Gallery, Catalog Search, Printable Checklists) with CTAs bottom-aligned inside each card:
  - Browse set images → /gallery
  - Catalog search → /search
  - My Collection link → /collection (auth) or login
- brands.jpg on wheat/orange band below feature cards, widened to ~5px from white box edges, no border on orange box

Search page:
- Breadcrumbs + half-width left column: breadcrumbs, "Search the catalog" title, intro text, red **NOTE:** disclaimer
- Right half: notice box (when designed)
- Search form: year, jurisdiction (label: "Jurisdiction (state/prov/country)"), set name, company, set type checkboxes (m/c/s/x)
- Form hides when results show; "Try another search" toggles visibility; intro text inside show/hide
- 728×90 gray (#777) ad placeholder above and below results (white centered text "728x90 banner advertisement")
- Grid view: 3 columns, gray card background, black border removed, plate image in same card as info, orange 2px pricing table borders, pricing anchored above two dark blue buttons, empty placeholder cells if row has fewer than 3
- List view: full-width rows, image left in black-padded box, info + pricing + buttons right; list text 15% larger; 10px margin right of buttons; pricing table same height as buttons; orange 2px borders; centered table text
- Toggle grid/list; pagination 12 per page; custom pagination with << (to page 2 when page 1 hidden) and >> (to last page when last hidden)
- plate_missing.png when no image

Gallery index (/gallery):
- Title/breadcrumb: "Gallery" / "Browse set images"
- 4-across set cards, gray background, black border matching search cards
- Random front image from set folder per card
- Set name, company (smaller), set code line, cat ref line ("ref: {value}" salmon color, or unknown)
- Aligned text rows across cards (fixed image div height ~180px)

Gallery show (/gallery/{setName}):
- setName is URL-encoded set_name from DB
- Header row: set title left, small "Return to Gallery" button right (same line)
- 4-column image grid, larger images
- Each cell: gray background, image in black box with 10px pad, jurisdiction centered below image in small text, variety in parentheses after jurisdiction
- Hover: flip front (_a) to back (_b); preload back images on page load
- Match image files to DB via image_base + jurisdiction logic (resolveSetImageDirectory, collectSetImages)

History (/history):
- Vertical accordion (NOT hover modals) — expand/collapse sections
- Each entry: 60×60 circular thumb (zoomed crop of image), wheat-colored label row (bold white text on colored background), expandable body
- Body: justified serif text (readable font), paragraph indent, single blank line between paragraphs
- Image floated right ~50% modal width with black background + light gray italic caption beneath image
- Click outside accordion panel does NOT close (accordion behavior only)
- Data from history_timeline_entries table; seed from config on first migration if empty

Articles (/articles, /articles/{slug}):
- List published articles only; keyword search with highlighted terms in titles AND body (use mark/highlight class)
- Fields: author, title, subtitle, slug, body, hero image, multiple article_images with alt/caption/sort_order
- Draft vs published: is_published + published_at; admin must click Publish to go live

About: owner proheadshot.jpg with site story

Contribute: contact form, honeypot field "company", email to site owner

================================================================================
4. ROUTES (routes/web.php)
================================================================================

Public:
GET  /                          home
GET  /ads.txt                   AdSense ads.txt line from config
GET  /gallery                   gallery.index
GET  /gallery/{setName}         gallery.show
GET  /search                    search
GET  /sitemap.xml               dynamic sitemap
GET  /about                     about
GET  /history                   history
GET  /articles                  articles.index
GET  /articles/{slug}           articles.show
GET  /contribute                contribute
POST /contribute                contribute.store
POST /newsletter/subscribe      newsletter.subscribe

Guest:
GET/POST /login, /register

Auth + not.blocked:
GET  /profile, PUT /profile
GET  /collection
POST /collection
GET  /collection/manage
PUT  /collection/manage
POST /collection/manage/fill
GET  /collection/manage/pdf
GET  /collection/{item}/edit
PUT  /collection/{item}
DELETE /collection/{item}
PUT  /collection/sets/{setCode}/visibility
GET  /collection/members/{username}

Auth + not.blocked + admin (/admin):
- dashboard, users CRUD, newsletter list/delete
- home-hero: settings, slides CRUD, fill_slide option
- history-timeline CRUD + delete with confirmation
- articles CRUD + publish action
- catalog: sets CRUD, plates CRUD, CSV import + template download

POST /logout (auth)

Middleware:
- EnsureUserIsAdmin (admin)
- EnsureUserIsNotBlocked (not.blocked)

================================================================================
5. DATABASE SCHEMA (migrations)
================================================================================

plates — catalog pricing guide, one row per plate type/variety:
  id, set_code(64), set_name(255), cat_ref(10)?, company(128)?,
  image_base(128)?, image_ext(16)?, has_back_image(tinyint)?,
  jurisdiction(128)?, jurisdiction_type(32)? — us_state|ca_province|ca_territory|foreign_country,
  year(int)?, serial_number(64)?, width_inches/height_inches decimal(10,4)?,
  value_mt, value_ex, value_vg, value_g, value_fr, value_po (varchar 32)?,
  variety_key(32)?, variety_notes(text)?, state_embossed/legend_embossed(tinyint)?,
  notes(text)?, sort_order(int default 0), timestamps
  UNIQUE(set_code, image_base, variety_key)
  Indexes on set_code, jurisdiction, year, company, cat_ref, etc.

users — Laravel default + username(30 unique), phone, address, profile_image,
  is_admin, is_blocked, blocked_at, blocked_reason
  Bootstrap admin: config admin.bootstrap_email promoted on migration

collection_items:
  user_id, plate_id, quantity, condition(4 chars MT/EX/VG/G/FR/PO),
  acquired_date, price_paid, storage_location, notes, is_wanted
  UNIQUE(user_id, plate_id)

collection_set_settings:
  user_id, set_code, is_public boolean

newsletter_subscribers:
  email unique, subscribed_at

hero_settings: interval_ms (default 7000)
hero_slides: sort_order, image, alt, headline, subline, cta, route, route_params(json), bg, is_active, fill_slide

history_timeline_entries:
  slug, sort_order, label, title, body, image, alt, caption, is_published

articles: slug, author, title, subtitle, body, hero_image, hero_image_alt, sort_order, is_published, published_at
article_images: article_id, image_path, alt, caption, sort_order

Standard Laravel: sessions, cache, jobs, password_reset_tokens tables.

================================================================================
6. DATA IMPORT & IMAGES
================================================================================

Artisan command: php artisan plates:import [csv_path] [--truncate]
- Default CSV: docs/Mini Plate Checklist - all_plates.csv
- Map imAge_A typo column → image_base
- Map jurisdiction_type: canada→ca_province, foreign/territory→foreign_country
- Empty image_base → nophoto-{rowNum}; empty variety_key → base
- Duplicate unique key → append -2, -3 to variety_key

Plate images on disk (NOT in git):
  {WebPublicPath}/plates/{set_code}/{image_base}_a.{ext}
  {WebPublicPath}/plates/{set_code}/{image_base}_b.{ext}  (optional back)

Other media folders under WebPublicPath:
  hero/, history-media/, articles-media/, profile uploads
  plate_missing.png, header banners, background, footer assets, setinfo/

Display dimensions: if width/height < 1 inch, show without leading zero (.625 not 0.625)

Image optimization (GD):
- ImageOptimizer class with profiles: plate, hero, history, article, profile
- Artisan: php artisan images:optimize {plates|articles-media|history-media|hero|all} [--set=code] [--dry-run]
- Default target: plates; dry-run shows folder path + count only (not file list)
- Optimize on upload in admin for hero, history, articles, profile, catalog plate uploads

Article images: use stable filenames (e.g. variety-post.jpg) not random hashes in public URL

================================================================================
7. MEMBER COLLECTION FEATURES
================================================================================

/collection index:
- Abbreviated list of sets user has entered data for
- Link to manage each set
- Per-set public/private toggle (collection_set_settings)
- Section: other members' public collections — username link, total quantity owned

/collection/manage:
- Pick set by search/dropdown
- Full set as editable table: quantity, condition, notes, wanted flag, etc.
- "Fill set" action: default quantity + condition applied to all plates in set
- Show catalog value for user's chosen condition (private total)
- PDF export: letter-size checklist (full set or user's owned items only) via dompdf

/collection/members/{username}:
- View another user's public set data; display @username correctly in Blade (escape @ as @@)

Profile /profile:
- Edit name, email, username, phone, address, profile photo upload
- Live preview on file select before save
- Clear label for remove-photo checkbox (plain language)

Registration/login: username required, unique; blocked users cannot access member routes

================================================================================
8. ADMIN CMS
================================================================================

Dashboard linking to all modules.

Users: list, view, block/unblock with reason, grant is_admin, delete

Newsletter: list subscribers, delete

Home hero: edit rotation interval; CRUD slides with image upload, headline, subline, CTA, route link, background color/gradient, fill_slide checkbox for full-bleed image

History timeline: CRUD, image upload to history-media/, caption, sort order, delete with JS confirm

Articles: CRUD, multiple image uploads, draft/publish button sets is_published + published_at

Catalog:
- Sets index with compact row height (5px vertical padding on text)
- Add set form: centered 700px box; row1 set_code (~8 chars wide input) + set_name (~35 chars); row2 year (~5 chars) + company (~45 chars); spacing above submit button
- Set show: plate list, edit/delete
- Plate create/edit: all plate columns, image upload to plates/{set_code}/
- CSV import page: download template matching plates schema, upload CSV for bulk insert into one set

Catalog set form and import follow docs/PLATE_CSV_COLUMNS_REFERENCE.html column definitions.

================================================================================
9. SEO & ADS
================================================================================

layouts/app.blade.php:
- @yield title, meta_description, canonical_url
- JSON-LD structured data where appropriate (WebSite on home)
- AdSense script partial when config enabled

SitemapController: include home, gallery, search, history, articles, about, published article URLs

Home page: keyword-rich but readable prose; dynamic plate count in meta

config/adsense.php: publisher ID, enable flag
GET /ads.txt → google.com, pub-{id}, DIRECT, f08c47fec0942fa0

Search results: 728×90 placeholder components (real ad units later)

================================================================================
10. LAYOUT & BLADE STRUCTURE
================================================================================

resources/views/layouts/app.blade.php — minimal head, no complex unclosed @if
components/header.blade.php, footer.blade.php
components/home-hero-rotator.blade.php
components/search/*, components/ads/* as needed
views: home, about, history, contribute, search, gallery/index, gallery/show
views: articles/index, articles/show
views: collection/*, profile/edit
views: admin/* for all CMS modules
views: auth/login, register
PDF view: collection/pdf/set-checklist.blade.php

Keep layout simple to avoid Blade parse errors.

================================================================================
11. KEY BUSINESS RULES
================================================================================

- plates table is a PRICING GUIDE (six condition columns), not inventory of physical objects
- serial_number = characters printed ON the plate, not catalog ID
- cat_ref = catalog reference number (varchar 10)
- company = issuer/manufacturer (Post, General Mills, Topps, etc.)
- Many plates have NULL image_base = no photo; show plate_missing.png
- has_back_image: 1 = back exists at {base}_b.{ext}, 0 = front only, NULL = unknown
- Search set types: first letter of set_code — m=metal, c=cards, s=stickers, x=anything else (multi-select OR)
- Gallery sorted by MIN(year), set_code
- Search results sorted by set_name, sort_order, jurisdiction
- Contribute emails: cdcoppercoins@gmail.com (configurable)
- External shop link: https://www.ebay.com/str/minilicenseplates

================================================================================
12. DEPLOY & OPS ARTIFACTS
================================================================================

Create:
- deploy/deploy.config.json
- scripts/Build-DeployRelease.ps1
- deploy/public_html-index.php (points to ../laravel)
- deploy/site-check.php, setup-storage-link.php
- docs/DEPLOY_PLAIN_ENGLISH.md (one step at a time for non-technical owner)
- docs/PLATE_CSV_COLUMNS_REFERENCE.html
- GALLERY_CATALOG_SPEC.md, PROJECT_REFERENCE.md, AGENTS.md

Production .env: APP_ENV=production, DB credentials for minilp_minilp on server
After deploy: php artisan migrate --force; clear storage/framework/views/*.php
Promote admin on production via DB or bootstrap email config

Planned but not required for v1 launch: AWS S3/CloudFront (document in docs/AWS_IMAGES_SETUP.md)

================================================================================
13. IMPLEMENTATION ORDER
================================================================================

Phase 1: Laravel skeleton, layout/CSS, home, about, contribute, static assets
Phase 2: plates migration + import command + seed from CSV
Phase 3: Gallery (DB-driven) + Search (grid/list/pagination)
Phase 4: Auth, profile, collection tracker, PDF, public member browse
Phase 5: Admin users + catalog CRUD + CSV import
Phase 6: Hero rotator admin, history accordion + CMS, articles + CMS
Phase 7: Newsletter, SEO sitemap, AdSense hooks, image optimizer command
Phase 8: Deploy scripts + documentation

Build each phase completely before moving on. Match visual design precisely. Test on localhost:8000 with XAMPP MySQL.

================================================================================
14. DEFINITION OF DONE
================================================================================

A fresh clone with MySQL running, migrations, plates:import, and public/plates/ folders populated should produce a site indistinguishable in function and layout from minilicenseplates.com as described above: same routes, same member/admin capabilities, same search/gallery behavior, same admin CMS modules, same deploy model, same gitignore rules.

Do not add features not listed unless needed for correctness. Do not simplify the search UI or remove collection valuation/PDF. Do not put plate binary files in git.
```

---

**File location:** `d:\aamlpproj\docs\MiniLicensePlates_MASTER_BUILD_PROMPT.md`

**How to use:** Open this file, copy the entire block between the triple backticks, and paste it as your opening prompt in a new AI session.

**PDF version:** `docs/MiniLicensePlates_MASTER_BUILD_PROMPT.pdf` (formatted summary + full verbatim appendix)

**Companion docs:** `GALLERY_CATALOG_SPEC.md`, `PROJECT_REFERENCE.md`, `docs/PLATE_CSV_COLUMNS_REFERENCE.html`, `docs/MiniLicensePlates_Investor_Site_Plan.pdf`

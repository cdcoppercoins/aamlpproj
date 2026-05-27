# MiniLicensePlates.com – Project Reference

This file is the single source of truth for what has been done, how the project is set up, and what to do (or avoid) going forward. Use it so you don’t repeat work or give conflicting instructions.

---

## 1. What This Project Is

- **Site:** MiniLicensePlates.com – visual reference library for miniature license plate toys (candy/gum/cereal premiums).
- **Stack:** Laravel (PHP), MySQL (XAMPP), Blade views, CSS/images in `public/`.
- **Repo:** `d:\aamlpproj` – connected to GitHub as `cdcoppercoins/aamlpproj`. Use **GitHub Desktop** for commits/pushes; avoid using Cursor’s git to prevent lock-file issues.

---

## 2. What Was Done (Migration & Setup)

### Migration from original PHP site
- Original site code was in **mlp_code/** (copied from user). That folder is **not** part of the app anymore; it was only the source for migration.
- **Layout:** `resources/views/layouts/app.blade.php` – minimal layout, no `@if`/`@isset` in head (to avoid Blade parse errors).
- **Shared parts:** `resources/views/components/header.blade.php`, `footer.blade.php`, `modal_script.blade.php`.
- **Pages:** Home, About, History, Contribute, Gallery (index + per-set show) – all as Blade views under `resources/views/`.
- **Routes:** `routes/web.php` – named routes: `home`, `gallery`, `gallery.show`, `about`, `history`, `contribute`, `contribute.store`.
- **Controllers:** `GalleryController` (gallery index + show with folder map and image scanning), `ContributeController` (form + mail to cdcoppercoins@gmail.com, honeypot `company`).
- **Gallery:** Reads images from `public/plates/{setCode}/` (e.g. `m88p`, `c36g`). Expects files like `*_a.jpg` (front) and `*_b.jpg` (back); thumbnails from first `*a.*` per set.
- **Setinfo:** Blade partials in `resources/views/setinfo/` (e.g. `m88p_info.blade.php`, `m88p_varieties.blade.php`). Setinfo images in `public/setinfo/` (e.g. `m88p_img.jpg`).
- **Assets:** `public/main.css`, `public/header_banner.png`, `public/background_img.jpg` – copied from original site. No Vite/build step required for this project’s current setup.

### Fixes and decisions
- **Blade parse error:** Resolved by keeping layout free of unclosed `@if` and by removing raw `.php` files from `resources/views/setinfo/` (only `.blade.php` remain).
- **Storage/cache permissions (Windows):** User was instructed to fix `storage` and `bootstrap/cache` permissions (e.g. via Properties → Security or `icacls`) when Laravel couldn’t write.
- **Database:** Switched from SQLite to **MySQL (XAMPP)**. No database was ever associated with the domain before; this is a new DB.
- **MySQL:** Database **minilicenseplates** created via Laravel (“Would you like to create it?” → yes). User confirmed MySQL root has **empty password**; `.env` has `DB_PASSWORD=` (empty).
- **Sessions/cache/queue:** Use MySQL: `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`. Migrations were run (users, sessions, cache, cache_locks, jobs tables exist).

### Git and ignored paths
- **mlp_code/** – added to `.gitignore`. Original site source; not needed to run the app. Do not add or commit.
- **public/plates/** – in `.gitignore`. All plate images live here (user copied from live site via FileZilla). Not in repo; must exist on each environment.
- **.env** – ignored (standard). DB and app config live here.

---

## 3. Current Configuration Summary

| Item | Value |
|------|--------|
| App URL (local) | http://localhost:8000 (or XAMPP vhost if configured) |
| Database | MySQL, DB name: `minilicenseplates` |
| DB user | root, no password |
| Session/Cache/Queue | database (MySQL) |
| Plate images | `public/plates/{setCode}/` (e.g. m88p, c36g) – local only, not in git |
| Contact form email | cdcoppercoins@gmail.com |

---

## 4. Key File and Folder Locations

- **Routes:** `routes/web.php`
- **Controllers:** `app/Http/Controllers/GalleryController.php`, `ContributeController.php`
- **Layout:** `resources/views/layouts/app.blade.php`
- **Views:** `resources/views/home.blade.php`, `about.blade.php`, `history.blade.php`, `contribute.blade.php`, `gallery.blade.php`, `gallery/show.blade.php`
- **Setinfo views:** `resources/views/setinfo/*.blade.php`
- **Components:** `resources/views/components/header.blade.php`, `footer.blade.php`, `modal_script.blade.php`
- **Public assets:** `public/main.css`, `public/header_banner.png`, `public/background_img.jpg`, `public/setinfo/`, `public/plates/`
- **Migrations:** `database/migrations/` (already run for users, sessions, cache, jobs)

---

## 5. What NOT To Do / What’s Already Decided

- Do **not** add **mlp_code** or **public/plates** to the repo (both are in `.gitignore`).
- Do **not** tell the user to create a MySQL database manually for this app – it was already created via `php artisan migrate` (create DB when prompted).
- Do **not** suggest SQLite for this project – user chose MySQL (XAMPP).
- Do **not** use Cursor for git commits; use GitHub Desktop to avoid `.git/index.lock` issues.
- Do **not** add `@if(isset(...))` in the main layout without matching `@endif`; keep layout simple to avoid Blade parse errors.
- Do **not** put raw `.php` files in `resources/views/`; use `.blade.php` only.

---

## 6. Turn on site (saved instruction)

**When the user says "turn on site", do the following in order:**

1. **MySQL must be running** (database for app, sessions, cache).
   - User uses XAMPP: they must start **MySQL** in XAMPP Control Panel (green = running).
   - Assistant cannot start XAMPP/MySQL programmatically; remind user to start MySQL in XAMPP if the site fails with a database error.

2. **Start the Laravel development server** (serves the site at http://localhost:8000).
   - From project root `d:\aamlpproj`: run `php artisan serve`.
   - Run it in the background so it keeps running (e.g. `php artisan serve` as a background command).
   - If a server is already running on port 8000, either use that or stop the old process and start again.

3. **Tell the user:**
   - Site URL: **http://localhost:8000**
   - Open that in a browser. If it doesn’t load, confirm MySQL is running in XAMPP and that `php artisan serve` is still running.

**Checklist – everything that must be running for the site to work locally:**

| Requirement        | What to do / note |
|--------------------|--------------------|
| MySQL              | Start in XAMPP Control Panel (user action). DB: `minilicenseplates`, user: root, no password. |
| Laravel web server | Run `php artisan serve` from `d:\aamlpproj` (assistant can start this). Serves at http://localhost:8000. |
| PHP                | Must be on PATH (XAMPP or standalone). Required for `php artisan serve`. |
| Project files      | Code and `public/` assets (e.g. main.css, header_banner.png) and `public/plates/` images must exist locally. |
| .env               | Must exist with `DB_CONNECTION=mysql`, `DB_DATABASE=minilicenseplates`, `DB_USERNAME=root`, `DB_PASSWORD=` (empty). |

**Summary:** For "turn on site": (1) Remind user to start MySQL in XAMPP if needed. (2) Start Laravel with `php artisan serve` from `d:\aamlpproj`. (3) Tell user to open http://localhost:8000.

---

## 7. How to Run the App (short)

- Start MySQL (XAMPP).
- From project root: `php artisan serve`.
- Open http://localhost:8000.

---

## 7b. Production deploy (use every time)

Server: **`/laravel/`** (app) + **`/public_html/`** (live web). Renamed from `new.minilicenseplates.com` — done.

**User-facing instructions (plain English, one step at a time):** **docs/DEPLOY_PLAIN_ENGLISH.md** — agents must read this when the user asks about deploy, PowerShell, FileZilla, or `Build-DeployRelease.ps1`.

Packager on PC: `cd d:\aamlpproj` then `.\scripts\Build-DeployRelease.ps1 -OpenFolder` (add `-IncludeVendor` if composer changed or 500/class errors). Upload `deploy\out\<timestamp>\laravel\` and `public_html\` via FileZilla. Config: `deploy/deploy.config.json`.

---

## 8. Optional / Future

- **GALLERY_CATALOG_SPEC.md** – full plan for database-backed catalog. See section 9 and that file for details.
- **docs/PLATE_CSV_COLUMNS_REFERENCE.html** – printable column reference for CSV import; defines plate data schema.
- **DOWNLOAD_IMAGES.md** / **download-plates.ps1** – used when plate images weren’t local; user later copied images via FileZilla. Can be ignored for normal use.
- **MIGRATION_NOTES.md** – original migration plan; kept for history. **PROJECT_REFERENCE.md** (this file) is the authoritative reference.

---

## 9. Planned Catalog (AI Reference)

*Consolidates catalog design for AI context. Full details in GALLERY_CATALOG_SPEC.md.*

### Purpose
Catalog pricing guide (not individual plates). Each row = one plate type/variety with six value columns. Gallery becomes searchable catalog; home page gets search form.

### Plates Table (Key Columns)
- set_code, set_name, cat_ref (varchar 10), company (issuer/manufacturer), image_base, image_ext (nullable; NULL = no photo), has_back_image (1/0/NULL)
- jurisdiction, jurisdiction_type (us_state, ca_province, ca_territory, foreign_country)
- year, serial_number (numbers/letters on the plate), width_inches, height_inches
- value_mt, value_ex, value_vg, value_g, value_fr, value_po (six conditions)
- variety_key, variety_notes (plate varieties)
- state_embossed, legend_embossed (1/0/NULL)

**Images**: Many plates have no photo. Back image optional at {base}_b.{ext} when front exists.

### Behavior
Browse by set; search from home; CMS panel (auth-protected) to add/edit sets and plates. CMS includes: drag-and-drop/browse for per-plate images; CSV bulk import for multiple plates.

### Key Files
Migration (plates), Plate model, CatalogController, admin controllers, catalog/search views, docs/PLATE_CSV_COLUMNS_REFERENCE.html for CSV schema.

### Conventions
MySQL (minilicenseplates), no SQLite. Do not add public/plates/ or mlp_code/ to git. Use GitHub Desktop for commits.

---

*Last updated to reflect: Laravel migration complete, MySQL (XAMPP) in use, plates and mlp_code gitignored, GitHub Desktop for git, catalog spec documented.*

# Gallery to Catalog Specification

This document describes the planned changes to convert the current gallery into a searchable catalog backed by a database. It replaces the file-scan-based gallery with a catalog listing that can browse sets/plates or search by criteria.

---

## 1. Current State (What Exists Now)

- **Gallery index** (`/gallery`): Lists all sets from a hardcoded `$folderMap`. For each set, checks if `public/plates/{setCode}/` exists and has at least one `*_a.*` image. Shows first match as thumbnail.
- **Gallery show** (`/gallery/{setName}`): Scans set folder for `*_a.*` and `*_b.*` image pairs. Displays thumbnails with hover-to-flip to back. No per-plate metadata.
- **Plate images**: Stored in `public/plates/{setCode}/` (e.g. `m88p`). Files like `{base}_a.jpg` (front), `{base}_b.jpg` (back). Folder is gitignored; images are local-only.
- **Setinfo**: Blade partials in `resources/views/setinfo/` for set-level info and varieties. Some sets (e.g. 1988 Post) are 50-plate state sets with implied state metadata, but this is not stored anywhere.

---

## 2. Target Behavior

### 2.1 Catalog Listing (Browse Mode)

- **Catalog page** replaces the gallery as the main listing.
- **Browse by set**: Like current gallery — show each set, then each plate in that set.
- **Data source**: Plates come from the database, not from scanning the filesystem. Image paths still point to `public/plates/{setCode}/` (files remain in place).

### 2.2 Search Mode

- **Search form on home page**: Form with one or more criteria (e.g. state, set, year, etc.).
- **Search results page**: Same catalog view, but filtered to plates matching the search. Displays results in a search-results style layout (e.g. "X plates found" header, then catalog-style grid).

### 2.3 Example Search Criteria (Initial)

| Criterion   | Description                                           |
|------------|--------------------------------------------------------|
| Jurisdiction     | US state, Canadian province/territory, or foreign country (e.g. Alabama, Ontario, Mexico) |
| Jurisdiction type| Filter by region type: US state, Canadian province, Canadian territory, Foreign country |
| Set              | Filter by set name (e.g. 1988 Post Cereal Plates)                          |
| Company          | Filter by company (e.g. Post, General Mills, Topps)                       |
| Year             | Filter by year / era if available                                           |
| Set code         | Filter by folder code (e.g. `m88p`)                                        |
| Cat ref          | Filter by catalog reference                                                 |
| Value/price      | Filter by value range for MT, EX, VG, G, FR, or PO                         |
| State embossed   | Whether the state/jurisdiction name on the plate is embossed (yes / no / any) |
| Legend embossed  | Whether the legend/slogan on the plate is embossed (yes / no / any)         |

*"Legend" refers to slogans or secondary text on the plate (e.g. "Land of Enchantment", "Sportsman's Paradise"). For embossed filters, the search form typically offers "Any" (no filter), "Yes", or "No"; "Any" omits the filter, while Yes/No match only plates with that value (NULL/unknown can be included or excluded based on UX preference).*

---

## 3. Database Design

### 3.1 Table: `plates`

Catalog pricing guide. Each row is a plate type (or variety) with values for six conditions (MT, EX, VG, G, FR, PO). One row per plate design or variety — same base plate with different holes/embossing = separate rows.

| Column       | Type         | Nullable | Description                                           |
|-------------|--------------|----------|-------------------------------------------------------|
| id               | bigint       | NO       | Primary key, auto-increment                           |
| set_code         | varchar(64)  | NO       | Folder code (e.g. `m88p`), matches `public/plates/{set_code}/` |
| set_name         | varchar(255) | NO       | Display name (e.g. "1988 Post Cereal Plates")          |
| cat_ref          | varchar(10)  | YES      | Catalog reference number                               |
| company          | varchar(128) | YES      | Company that issued/manufactured the plate (e.g. Post, General Mills, Topps) |
| image_base       | varchar(128) | YES      | Base filename without `_a`/`_b` suffix (e.g. `AL_a` → `AL`). NULL = no photo for this listing. |
| image_ext        | varchar(16)  | YES      | Extension for front image (e.g. `jpg`). NULL when no photo. Back image optional: `{base}_b.{ext}` when present. |
| has_back_image   | tinyint      | YES      | 1 = back image exists, 0 = front only, NULL = unknown |
| jurisdiction     | varchar(128) | YES      | Region/country name: US state, CA province/territory, or foreign country (e.g. "Alabama", "Ontario", "Mexico") |
| jurisdiction_type| varchar(32)  | YES      | One of: `us_state`, `ca_province`, `ca_territory`, `foreign_country` |
| year             | int          | YES      | Year of plate / set if known                          |
| serial_number    | varchar(64)  | YES      | Numbers or letters displayed on the plate itself    |
| width_inches     | decimal(10,4) | YES     | Width in inches (e.g. 0.0625, 1.625). Up to 4 decimal places (thousandths). |
| height_inches    | decimal(10,4) | YES     | Height in inches (e.g. 0.0625, 1.625). Up to 4 decimal places (thousandths). |
| value_mt         | varchar(32)  | YES      | Value for Mint condition (e.g. 3.50 or 3-5)          |
| value_ex         | varchar(32)  | YES      | Value for Excellent condition                        |
| value_vg         | varchar(32)  | YES      | Value for Very Good condition                        |
| value_g          | varchar(32)  | YES      | Value for Good condition                             |
| value_fr         | varchar(32)  | YES      | Value for Fair condition                              |
| value_po         | varchar(32)  | YES      | Value for Poor condition                              |
| variety_key      | varchar(32)  | YES      | Short identifier for variety (e.g. "base", "4h", "6e"). Empty = base plate. Required to distinguish multiple rows for same plate. |
| variety_notes    | text         | YES      | Variety differences (e.g. "4-hole embossed", "6-hole flat", "large font") |
| state_embossed   | tinyint      | YES      | Is state/jurisdiction name embossed? 1=yes, 0=no, NULL=unknown |
| legend_embossed  | tinyint      | YES      | Is legend/slogan embossed? 1=yes, 0=no, NULL=unknown |
| notes            | text         | YES      | Optional notes                                       |
| sort_order       | int          | YES      | Display order within set (default 0)                   |
| created_at       | timestamp    | YES      |                                                       |
| updated_at       | timestamp    | YES      |                                                       |

**Unique constraint**: `(set_code, image_base, variety_key)` — one record per plate type or variety. Use `variety_key` = empty or "base" for the base plate; use values like "4h", "6e" for varieties. For plates with no photo, use a placeholder for `image_base` (e.g. `nophoto-1`) to satisfy uniqueness, or use a different scheme (e.g. unique on `serial_number` when `image_base` is null).

**Indexes** (for search performance):

- `plates_set_code`
- `plates_has_back_image`
- `plates_jurisdiction`
- `plates_jurisdiction_type`
- `plates_year`
- `plates_serial_number`
- `plates_width_inches`
- `plates_height_inches`
- `plates_set_name`
- `plates_cat_ref`
- `plates_company`
- `plates_variety_key`
- `plates_state_embossed`
- `plates_legend_embossed`

### 3.2 Jurisdiction Handling (US, Canada, Foreign)

The `jurisdiction` and `jurisdiction_type` columns together support:

| Type             | Examples                                                                 |
|------------------|--------------------------------------------------------------------------|
| `us_state`       | Alabama, Alaska, Texas, California, … (50 US states + DC if applicable) |
| `ca_province`    | Ontario, Quebec, British Columbia, Alberta, … (10 provinces)           |
| `ca_territory`   | Yukon, Northwest Territories, Nunavut (3 territories)                   |
| `foreign_country`| Mexico, Bahamas, etc. — store the country name in `jurisdiction`         |

**Lookup/reference**: Maintain a config or `jurisdictions` reference table with canonical names and types so the search form and import logic use consistent values. Example structure:

- **US states**: Standard full names (Alabama, Alaska, …). Map from 2-letter codes (AL, AK) when importing from filenames.
- **Canadian provinces**: Ontario, Quebec, British Columbia, Alberta, Manitoba, Saskatchewan, Nova Scotia, New Brunswick, Newfoundland and Labrador, Prince Edward Island.
- **Canadian territories**: Yukon, Northwest Territories, Nunavut.
- **Foreign countries**: Use consistent display names (e.g. "Mexico", "The Bahamas"). Add as needed when new sets are cataloged.

### 3.3 Value Columns (MT, EX, VG, G, FR, PO)

Each listing has six value columns (catalog pricing guide, not individual plates):

| Column   | Condition | Description                         |
|----------|-----------|-------------------------------------|
| value_mt | Mint      | Value in Mint condition             |
| value_ex | Excellent | Value in Excellent condition        |
| value_vg | Very Good | Value in Very Good condition        |
| value_g  | Good      | Value in Good condition             |
| value_fr | Fair      | Value in Fair condition             |
| value_po | Poor      | Value in Poor condition             |

Use numeric values or ranges (e.g. `3.50` or `3-5`). Leave blank if N/A for that condition.

### 3.4 Plate Varieties

Plates of like issue with differences are separate catalog entries (separate rows). Variety attributes:

| Column        | Description                                                                 |
|---------------|-----------------------------------------------------------------------------|
| variety_key   | Short ID: "base" or blank for base; "4h", "6e", etc. for varieties. Required for uniqueness. |
| variety_notes | Free-text description of differences (e.g. "4-hole embossed", "6-hole flat", "large font") |

Example: Alabama base plate (variety_key=base) + Alabama 4-hole embossed (variety_key=4h, variety_notes="4-hole embossed") + Alabama 6-hole flat (variety_key=6f, variety_notes="6-hole flat") = three rows with same `set_code` and `image_base`, different `variety_key` and `variety_notes`.

### 3.5 Images: No Photo, Front, Optional Back

- **Plates with no photo**: Many listings will have no images. Set `image_base` and `image_ext` to NULL/blank. The catalog displays a placeholder (e.g. "No image") for these entries.
- **Plates with photos**: `image_base` and `image_ext` point to the front image at `public/plates/{set_code}/{base}_a.{ext}`.
- **Optional back image**: When present, the back image is at `{base}_b.{ext}` in the same folder. Use `has_back_image` (1 = yes, 0 = no, NULL = unknown) so the UI knows whether to show hover-to-flip or "view back" without checking the filesystem.

### 3.6 CMS / Admin Panel

A **CMS panel** (not visible to public site visitors) for managing catalog content:

- **Authentication**: Admin-only access (e.g. Laravel auth middleware, or dedicated admin guard).
- **Add sets**: Create new sets (set code, display name, year). Sets define the folders under `public/plates/{code}/`.
- **Add plates**: Add plates to a set — all plate fields (jurisdiction, serial_number, values, variety, images, etc.). Support plates with no photo.
- **Edit/delete**: Update or remove sets and plates.
- **Image upload per plate**: For each plate (add or edit), provide drag-and-drop or browse-to-select for:
  - Front image (required when plate has a photo)
  - Back image (optional). When uploaded, set `has_back_image` = 1. Files saved as `{base}_a.{ext}` and `{base}_b.{ext}` in `public/plates/{set_code}/`.
- **CSV bulk import**: Upload a CSV file to add multiple plates at once. CSV columns must match the schema (see `docs/PLATE_CSV_COLUMNS_REFERENCE.html`). Import creates plate records; images are not in CSV — upload those per-item afterward, or pre-place files in `public/plates/{set_code}/` and use matching `image_base`/`image_ext` in the CSV.

Routes (example): `GET/POST /admin`, `GET/POST /admin/sets`, `GET/POST /admin/sets/{code}/plates`, `POST /admin/plates/import-csv`, etc. All under auth protection.

### 3.7 Optional: `sets` Table

If set metadata should be normalized (to avoid repeating set names and to add set-level fields):

| Column       | Type         | Nullable | Description                                   |
|-------------|--------------|----------|-----------------------------------------------|
| id          | bigint       | NO       | Primary key                                   |
| code        | varchar(64)  | NO       | Folder code (unique)                          |
| name        | varchar(255) | NO       | Display name                                  |
| year        | int          | YES      | Year of set                                   |
| ...         |              |          | created_at, updated_at                        |

Then `plates.set_code` becomes a foreign key to `sets.code`, and `plates.set_name` can be dropped in favor of a join. This can be a follow-up migration if desired. The CMS would manage the `sets` table directly.

---

## 4. Implementation Outline

### 4.1 Migration

1. Create migration for `plates` table (and optionally `sets`).
2. Seed or import: Populate `plates` from existing `$folderMap` + filesystem scan (one-time script or artisan command). For US state sets (e.g. 1988 Post), derive jurisdiction from filename where possible (e.g. `AL_a.jpg` → Alabama, `jurisdiction_type` = `us_state`) using a state-code lookup. For Canadian provinces/territories, use code lookups (ON → Ontario, etc.). For foreign country plates, populate `jurisdiction` and `jurisdiction_type` = `foreign_country` from known mappings or manual data entry. `state_embossed` and `legend_embossed` start as NULL; can be filled manually or in a later import pass.

### 4.2 Controllers & Routes

| Route                    | Controller / Action      | Purpose                                                |
|--------------------------|---------------------------|--------------------------------------------------------|
| `GET /`                  | HomeController@index      | Home page with search form                             |
| `GET /catalog`           | CatalogController@index   | Full catalog (all sets) or browse by set               |
| `GET /catalog/set/{code}`| CatalogController@set    | Plates in one set                                      |
| `GET /catalog/search`    | CatalogController@search  | Search results (query params: jurisdiction, jurisdiction_type, set, year, value, variety_key, state_embossed, legend_embossed) |

*Existing `gallery` and `gallery.show` routes can remain as redirects to the new catalog during transition, or be removed once catalog is live.*

### 4.3 Views

- **Home** (`home.blade.php`): Add search form (jurisdiction, jurisdiction type, set, year, value/price range, variety, state-embossed, legend-embossed) → submits to `/catalog/search`.
- **Catalog index** (`catalog/index.blade.php`): Browse view — list sets, each linking to `/catalog/set/{code}`. Or grid of all plates if desired.
- **Catalog set** (`catalog/set.blade.php`): All plates in one set (same visual style as current `gallery/show`).
- **Catalog search** (`catalog/search.blade.php`): Same layout as catalog set, but with "Search results for …" header and filtered plate list.

### 4.4 Data Flow

- **Catalog browse**: `CatalogController@index` → `Plate::groupBy('set_code')` or similar; `CatalogController@set` → `Plate::where('set_code', $code)->orderBy('sort_order')`.
- **Search**: `CatalogController@search` → `Plate::when($jurisdiction, fn($q) => $q->where('jurisdiction', $jurisdiction))->when($jurisdictionType, fn($q) => $q->where('jurisdiction_type', $jurisdictionType))->when($stateEmbossed !== null, fn($q) => $q->where('state_embossed', $stateEmbossed))->when($legendEmbossed !== null, fn($q) => $q->where('legend_embossed', $legendEmbossed))->when($setCode, ...)` etc. Return filtered collection to `catalog/search` view.

---

## 5. Migration Path from Current Gallery

1. Add migration and model for `plates`.
2. Create seed/artisan command to populate `plates` from existing folders (reuse current folder map + scan logic).
3. Implement `CatalogController` and catalog views.
4. Add search form to home, wire to search route.
5. Update nav/links: "Gallery" → "Catalog" (or keep "Gallery" label if preferred).
6. Optionally: redirect `gallery` and `gallery.show` to catalog equivalents.
7. Remove or deprecate `GalleryController` when catalog is fully in use.

---

## 6. Open Questions

- **Jurisdiction mapping**: Maintain config or `jurisdictions` table for US state codes (AL→Alabama), Canadian province/territory codes (ON→Ontario, YT→Yukon), and foreign country names. Use when importing from filenames and populating search dropdowns.
- **Sets without jurisdiction data**: Many sets may not have jurisdiction. Search by jurisdiction only returns plates where `jurisdiction` is set.
- **Embossed data source**: `state_embossed` and `legend_embossed` require manual cataloging or a separate import. Consider an admin UI or batch-edit workflow for filling these after initial import.
- **Image existence**: DB stores paths; actual files live in `public/plates/`. Many plates have no photos — display a placeholder. For those with photos, the back image (`{base}_b.{ext}`) is optional. Handle 404s gracefully.
- **Download/import flow**: If new images are added (e.g. via DownloadPlates command), how to sync new plates into DB? Manual re-run of seed command, or an "import/sync" artisan command.

---

## 7. File Summary

| File / Location                         | Action                                      |
|----------------------------------------|---------------------------------------------|
| `config/jurisdictions.php`             | Create (optional) — US states, CA provinces/territories, foreign countries lookup |
| `database/migrations/xxxx_create_plates_table.php` | Create                                      |
| `app/Models/Plate.php`                  | Create                                      |
| `app/Http/Controllers/CatalogController.php` | Create                                      |
| `database/seeders/PlatesSeeder.php`     | Create (or use artisan command for import)  |
| `routes/web.php`                       | Add catalog + search routes                 |
| `resources/views/home.blade.php`       | Add search form                             |
| `resources/views/catalog/index.blade.php`    | Create                                      |
| `resources/views/catalog/set.blade.php`      | Create                                      |
| `resources/views/catalog/search.blade.php`   | Create                                      |
| `app/Http/Controllers/GalleryController.php` | Deprecate / redirect when catalog ready    |
| `routes/web.php` (admin)                    | Add admin routes (auth-protected)          |
| `app/Http/Controllers/Admin/*`              | Admin controllers for sets and plates      |
| `resources/views/admin/*`                   | CMS views for managing sets and plates     |

---

*Last updated: February 2026*

# AI Agent Context – MiniLicensePlates.com

Use **PROJECT_REFERENCE.md** as the primary source of truth for project setup, conventions, and current state.

## Key References

| Document | Purpose |
|----------|---------|
| **PROJECT_REFERENCE.md** | Current state, config, what not to do, how to run |
| **docs/DEPLOY_PLAIN_ENGLISH.md** | **Read first** when user asks about deploy/upload — plain steps, one at a time, no jargon |
| **docs/DEPLOY_EASY.md** | Short technical deploy summary for agents |
| **docs/ONE_TIME_SERVER_LAYOUT.html** | One-time server rename (`new.minilicenseplates.com` → `laravel`) — likely already done |
| **GALLERY_CATALOG_SPEC.md** | Planned catalog: schema, routes, CMS, implementation |
| **docs/PLATE_CSV_COLUMNS_REFERENCE.html** | CSV column definitions for plate import |
| **docs/AWS_IMAGES_SETUP.md** | AWS S3/CloudFront image hosting — in progress; read when user returns from AWS support |

## Catalog Quick Reference

- **plates** table: catalog pricing guide with six value columns (MT, EX, VG, G, FR, PO)
- **cat_ref**: catalog reference (varchar 10)
- **company**: issuer/manufacturer (e.g. Post, General Mills, Topps)
- Many plates have **no photo** (image_base/image_ext nullable)
- **has_back_image** (1/0/NULL): back exists at `{base}_b.{ext}` when 1
- **CMS panel**: auth-protected admin to add sets and plates. Drag-and-drop/browse for per-plate images; CSV bulk import.
- **serial_number**: numbers/letters on the plate (not catalog ID)
- **width_inches, height_inches**: dimensions in inches
- Jurisdiction types: us_state, ca_province, ca_territory, foreign_country

## Conventions

- MySQL (minilicenseplates). Do not suggest SQLite.
- Do not add mlp_code/ or public/plates/ to git.
- Use GitHub Desktop for commits (avoid Cursor git).
- Blade only in views; no raw .php in resources/views/.

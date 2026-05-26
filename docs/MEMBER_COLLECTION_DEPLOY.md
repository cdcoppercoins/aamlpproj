# Member Collection Feature – Deploy Checklist

**Added:** 2026-05-24  
**Feature:** Username/password sign-in; personal collection tracking (quantity, condition, notes, want list).

---

## What was added

| Item | Details |
|------|---------|
| Routes | `/login`, `/register`, `/logout`, `/collection`, `/collection/manage`, `/collection/manage/pdf`, `/collection/{id}/edit` |
| Tables | `users.username`, `collection_items` |
| Nav | **SIGN IN** (guest) · **MY COLLECTION** (signed in) |

### `collection_items` columns

- `quantity` — how many owned (or wanted)
- `condition` — MT, EX, VG, G, FR, PO
- `acquired_date`, `price_paid`, `storage_location`, `notes`
- `is_wanted` — want list vs owned

---

## Deploy to minilicenseplates.com (cPanel)

### 1. Upload code

Via GitHub Desktop: commit and push, then pull on server **or** upload changed files via FileZilla to `/home/minilp/new.minilicenseplates.com/`.

**Composer dependency (PDF export):** run on the server from the app root:

```bash
composer install --no-dev --optimize-autoloader
```

This installs `barryvdh/laravel-dompdf` (added for collection PDF checklists). Upload an updated `vendor/` folder if you cannot run composer on the server.

**Files/folders that must be updated:**

- `app/Http/Controllers/Auth/` (new)
- `app/Http/Controllers/CollectionController.php` (new)
- `app/Http/Controllers/SearchController.php`
- `app/Models/CollectionItem.php` (new)
- `app/Models/User.php`
- `database/migrations/2026_05_24_100000_add_username_to_users_table.php` (new)
- `database/migrations/2026_05_24_100001_create_collection_items_table.php` (new)
- `routes/web.php`
- `resources/views/auth/` (new)
- `resources/views/collection/` (new)
- `resources/views/components/collection-add-form.blade.php` (new)
- `resources/views/components/flash-messages.blade.php` (new)
- `resources/views/components/gallery-result-card.blade.php`
- `resources/views/components/header.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/search.blade.php`
- `public/main.css`

Also copy `public/main.css` to `public_html/main.css`.

### 2. Run migrations on production

SSH (if available) from app root:

```bash
cd /home/minilp/new.minilicenseplates.com
php artisan migrate --force
```

If no SSH: use cPanel **Terminal** or phpMyAdmin is **not** enough — migrations must run via artisan.

Expected output: two new migrations (username + collection_items).

### 3. Verify production `.env`

Sessions already use database — required for login:

```env
SESSION_DRIVER=database
APP_URL=https://minilicenseplates.com
```

No new env vars required for collection feature.

### 4. Clear caches (SSH)

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Delete stale files in `bootstrap/cache/` if config errors appear (except `.gitignore`).

### 5. Smoke test on live site

1. Open `https://minilicenseplates.com/register` — create account
2. Sign in at `/login`
3. Catalog Search → run a search → **Add to collection** on a result
4. **MY COLLECTION** in nav → verify entry, edit quantity/condition, remove

---

## Local test

```powershell
cd d:\aamlpproj
php artisan serve
```

- Register: http://localhost:8000/register  
- Collection: http://localhost:8000/collection  

---

## Rollback (if needed)

```bash
php artisan migrate:rollback --step=2
```

This drops `collection_items` and removes `users.username`.

---

## Future enhancements (not in v1)

- Password reset email
- Export collection to CSV / printable checklist
- Add from gallery poster view
- Duplicate entries (same plate, different conditions)

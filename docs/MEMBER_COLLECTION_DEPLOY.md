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

## Deploy to minilicenseplates.com (cPanel + FileZilla)

### 1. Upload code

Via **GitHub Desktop**: commit and push (backup on GitHub).

Then upload changed files with **FileZilla** to `/new.minilicenseplates.com/` (same subfolders as local). CSS and JS go to `/new.minilicenseplates.com/public/`.

**One-time setup:** If you still upload CSS to both `public_html` and Laravel, follow **Part A** in `docs/PRODUCTION_UPDATE_DEPLOY.html` first to fix document root in cPanel.

**You do not need SSH, Terminal, or `git pull` on the server.**

If `composer.json` changed, run `composer install --no-dev` on your PC and upload the `vendor/` folder via FileZilla.

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
- `public/main.css` → `/new.minilicenseplates.com/public/main.css`
- `public/js/` → `/new.minilicenseplates.com/public/js/` (if present)

### 2. Run migrations on production

Upload new files from `database/migrations/` to `/new.minilicenseplates.com/database/migrations/` on the server.

Without Terminal/SSH, ask your host to run `php artisan migrate --force` once in your app folder, or use a one-time cPanel Cron Job. phpMyAdmin alone is not a substitute for migrations.

Expected output when run: migrations for username, collection_items, profile fields, admin fields, etc.

### 3. Verify production `.env`

Sessions already use database — required for login:

```env
SESSION_DRIVER=database
APP_URL=https://minilicenseplates.com
```

No new env vars required for collection feature.

### 4. Clear caches (optional)

In cPanel File Manager or FileZilla, under `/new.minilicenseplates.com/`, delete `.php` files in `bootstrap/cache/` (keep `.gitignore`). Delete stale files in `storage/framework/views/` if pages look wrong.

### 5. Test live

1. Open `https://minilicenseplates.com/register` — create account
2. Sign in at `/login`
3. **Profile** — add phone, address, optional photo
4. Catalog Search → run a search → **Add to collection** on a result
5. **MY COLLECTION** in nav → verify entry, edit quantity/condition, remove

**Profile photos:** ensure `public/storage` on the server points to profile images (your host may have run `php artisan storage:link` once during initial setup). Uploaded photos live under `storage/app/public/profile-images/`.

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

# Deploy Without Guessing

## Server layout (after one-time setup)

| Server folder | What it is |
|---------------|------------|
| **`/laravel/`** | PHP code, vendor, `.env` — not directly on the web |
| **`/public_html/`** | What **minilicenseplates.com** loads — CSS, images, `index.php`, `plates/` |

`public_html/index.php` loads Laravel from `../laravel`.

**One-time rename:** see **`docs/ONE_TIME_SERVER_LAYOUT.html`** (rename `new.minilicenseplates.com` → `laravel`).

---

## Every update

1. **Test locally** — XAMPP MySQL on, `php artisan serve`, http://localhost:8000  
2. **GitHub Desktop** — commit and push  
3. **Build package:**

   ```powershell
   cd d:\aamlpproj
   .\scripts\Build-DeployRelease.ps1 -OpenFolder
   ```

   Use `-IncludeVendor` after `composer.json` changes or class-not-found / 500 errors.

4. **Upload** (follow `UPLOAD_INSTRUCTIONS.txt` in the folder created):
   - `laravel\` → `/laravel/`
   - `public_html\` → `/public_html/`
   - **Or** upload `release.zip` to `/home/minilp/` and extract once  

   Never upload `.env` or `public/plates/` from your PC unless you added new photos.

5. **Clear cache** on server: delete `laravel/bootstrap/cache/*.php` and `laravel/storage/framework/views/*.php`  
6. **Test** — https://minilicenseplates.com (Ctrl+F5)

---

## Optional: GitHub → FTP

`docs/DEPLOY_GITHUB_FTP.md`

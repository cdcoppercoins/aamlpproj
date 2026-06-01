# One-command deploy

## One-time setup (5 minutes)

1. Copy `deploy\deploy.local.json.example` to `deploy\deploy.local.json`
2. Edit `deploy.local.json`:
   - **sshHost** — your server hostname (e.g. `minilicenseplates.com`)
   - **sshUser** — `minilp` (preferred; avoid `root` if you can)
3. Set up **SSH key login** so deploy does not ask for a password each time.

## Every deploy

Open PowerShell:

```powershell
cd d:\aamlpproj
.\scripts\Deploy-ToProduction.ps1
```

That script:

1. Builds the full upload package
2. Uploads **all** of `laravel/` and `public_html/` (no more picking individual files)
3. Clears view/cache/route/config caches on the server
4. Runs `php artisan migrate --force`
5. Checks that reports routes and views exist

After it says **Deploy finished**, open the live site and press **Ctrl+F5**.

## If composer changed

```powershell
.\scripts\Deploy-ToProduction.ps1 -IncludeVendor
```

## Notes

- **Catalog plate data** is still separate — use Admin → Catalog → Import on the live site.
- **New photos** — upload to `public_html/plates/` separately if needed.
- `deploy/deploy.local.json` is not committed to git (your server login details stay private).

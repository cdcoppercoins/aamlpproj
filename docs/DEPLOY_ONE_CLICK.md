# One-click deploy (local PC → live site)

Your PC cannot use SSH to the server. This uses **FTP** (same login as FileZilla) plus a **one-time server token** so migrations and cache clearing run automatically.

## One-time setup (about 10 minutes)

### 1. Create your private config file

1. In File Explorer open `d:\aamlpproj\deploy\`
2. Copy `deploy.local.json.example` → `deploy.local.json`
3. Edit `deploy.local.json`:
   - **ftpUser** / **ftpPassword** — same as FileZilla (`chuck@minilicenseplates.com`, etc.)
   - **deployApplyToken** — make up a long random string (e.g. 32 letters/numbers). Example: `mlp-deploy-8f3k2j9x4q7v1n6`

### 2. On the server (one time)

In **FileZilla** or cPanel File Manager, open `/home/minilp/laravel/.env` and add a **new line** at the bottom:

```env
DEPLOY_APPLY_TOKEN=your-same-token-as-deploy.local.json
```

Save. Do not upload `.env` from your PC on normal deploys.

### 3. Upload the finish helper (one time)

Run deploy once, or upload by hand:

- Local: `d:\aamlpproj\deploy\apply-deploy.php`
- Server: `/home/minilp/public_html/apply-deploy.php`

### 4. Optional: faster FTP

Install free [WinSCP](https://winscp.net/) on your PC. The deploy script uses it automatically when present (best for large `laravel.zip`).

### 5. If automatic FTP times out

Use FileZilla (you already know this), then let the script finish on the server:

Double-click **`Deploy-FileZilla-Finish.bat`** after uploads finish.

You should see a **black window** titled **MLP Deploy - FileZilla Finish** with cyan text and artisan output. If nothing appears, check the taskbar for a minimized Command Prompt window.

Or in PowerShell:

```powershell
cd d:\aamlpproj
.\scripts\Deploy-ToProduction.ps1 -ApplyOnly
```

---

## Every deploy — one click

**Double-click:** `d:\aamlpproj\Deploy-Now.bat`

Or in PowerShell:

```powershell
cd d:\aamlpproj
.\scripts\Deploy-ToProduction.ps1
```

The script will:

1. Build a full package (`laravel/` + `public_html/`) — no picking files by hand  
2. Upload **`public_html.zip`** then **`laravel.zip`** via FTP (or one `release.zip`)  
3. Call `apply-deploy.php` on the server (unzip, clear caches, `migrate --force`)  
4. Say **Deploy finished** when done  

Then open https://minilicenseplates.com and press **Ctrl+F5**.

### If composer changed on your PC

```powershell
.\scripts\Deploy-ToProduction.ps1 -IncludeVendor
```

---

## Alternative: GitHub button (no PC upload)

If you use GitHub: **Actions** → **Deploy to production** → **Run workflow**.  
Needs FTP secrets in the repo (see `docs/DEPLOY_GITHUB_FTP.md`).

---

## What this does *not* upload

- **`.env`** on the server — never overwritten  
- **`public/plates/`** photos — already on the server; add new photos via FileZilla to `public_html/plates/` if needed  
- **Catalog CSV rows** — use Admin → Catalog → Import on the live site  

---

## Troubleshooting

| Problem | Fix |
|--------|-----|
| FTP login failed | Check `ftpUser` / `ftpPassword` in `deploy.local.json` |
| Deploy finish failed | Check `DEPLOY_APPLY_TOKEN` in server `.env` matches `deploy.local.json` |
| apply-deploy.php missing | Upload `deploy/apply-deploy.php` to `public_html/` |
| FTP timeout on big zip | Install WinSCP, or use `-FtpManual` and upload with FileZilla |
| FTP `GetRequestStream` / System error | Use split zips or `-FtpManual`; do not upload thousands of loose files |
| Site still old after deploy | Ctrl+F5; run deploy again and check apply-deploy output mentions “Extracted” |
| 500 after deploy | Download `laravel/storage/logs/laravel.log` (last lines) |

---

## Security

- `deploy.local.json` is gitignored (password stays on your PC).  
- Delete `public_html/apply-deploy.php` if you stop using automated deploy.  
- Use a long random `DEPLOY_APPLY_TOKEN`; do not share it.

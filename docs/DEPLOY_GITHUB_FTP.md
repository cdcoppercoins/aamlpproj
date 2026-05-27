# Optional: Deploy from GitHub (one button)

Manual FileZilla works if you use `Build-DeployRelease.ps1` every time. If you want **push → live** without picking files, set up this workflow once.

## What it does

- You run **Actions → Deploy to production → Run workflow** in GitHub (or trigger on a tag you choose later).
- GitHub runs `composer install --no-dev`, then uploads the same file set as the PowerShell packager (excluding `.env`, `public/plates/`).
- You still clear `bootstrap/cache/*.php` on the server after deploy (or ask the host to add a post-deploy cron).

## One-time setup

1. GitHub repo → **Settings → Secrets and variables → Actions → New repository secret**  
   - `FTP_SERVER` = `ftp.minilicenseplates.com`  
   - `FTP_USERNAME` = `chuck@minilicenseplates.com`  
   - `FTP_PASSWORD` = your FTP password  

2. Commit `.github/workflows/deploy-production.yml` (already in the repo).

3. First run: **Actions** tab → **Deploy to production** → **Run workflow**.

4. Test https://minilicenseplates.com (Ctrl+F5).

## Dual folder (before Part A)

The workflow uploads Laravel to `/laravel/`. Also upload the packager’s **public_html** folder to `/public_html/` when CSS, JS, or images changed (or use `release.zip` from the deploy script).

## Security

- Never commit FTP password or `.env`.
- Prefer a dedicated FTP user with access only to `new.minilicenseplates.com` if your host allows it.

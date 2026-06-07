# Deploy (fast)

## One-time

1. `deploy\deploy.local.json` — FTP user/password + `deployApplyToken`
2. Install [WinSCP](https://winscp.net/eng/download.php)
3. Upload `deploy\apply-deploy.php` → `public_html/apply-deploy.php`

## Every deploy

**Double-click `Deploy-Now.bat`** → **Ctrl+F5** on the live site

## If `.env` is missing on server

1. **Double-click `Deploy-Fix-Env.bat`**
2. cPanel → **MySQL Databases** — copy database name, username, password into the form
3. FileZilla: delete `public_html/setup-env.php` after it says OK
4. **`Deploy-Now.bat`**

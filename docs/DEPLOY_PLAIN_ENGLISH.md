# How to update the live website (plain English)

**For:** Chuck — use this whenever you need to put local changes on https://minilicenseplates.com  
**For AI agents:** When the user asks about deploy, upload, FileZilla, PowerShell, `Build-DeployRelease`, or “how do I update the site,” read this file and give **one step at a time**. Do not use jargon without explaining it. Ask the user to reply **done** before the next step.

---

## What is on the server (remember this)

| Folder on server | What it is |
|------------------|------------|
| **`laravel`** | The “engine” — PHP code, database settings file (`.env`). Visitors never open this folder in a browser. |
| **`public_html`** | What people see — `index.php`, `main.css`, photos in `plates/`, etc. The domain points here. |

The old folder name **`new.minilicenseplates.com` is gone** — it was renamed to **`laravel`**.

**`public_html/index.php`** is a short file that tells the server: “load the real app from the `laravel` folder next door.”

---

## Words that confuse people (simple meanings)

| Term | Plain meaning |
|------|----------------|
| **Deploy / upload** | Copy your changed files from your PC to the web host so the live site updates. |
| **PowerShell** | A program on Windows where you type commands (blue window). Not FileZilla. |
| **`cd d:\aamlpproj`** | “Go to my project folder on the PC.” |
| **`.\scripts\Build-DeployRelease.ps1`** | A helper script that **copies** your project into a tidy “ready to upload” folder so you don’t miss files. |
| **`-OpenFolder`** | When the script finishes, **open that folder** in File Explorer automatically. |
| **FileZilla** | Program that copies files to the server (left = your PC, right = server). |
| **vendor** | A large folder of PHP libraries. If the live site shows “500” after a code update, rebuild the package **with vendor** (see below). |

---

## When you need to do this

Only when you **changed the site on your PC** and want the **live** site to match:

- You edited code, views, or CSS locally
- You tested on http://localhost:8000 and it looks right
- You committed in **GitHub Desktop** (backup)

You do **not** run this every day.

---

## The whole process (overview)

1. Test on your PC (localhost).
2. Commit in GitHub Desktop.
3. Run the **packager script** on your PC (creates upload folders).
4. Upload with **FileZilla** (or one zip in cPanel).
5. Delete a few **cache** files on the server.
6. Open the live site and press **Ctrl+F5**.

---

## STEP-BY-STEP — Pack files on your PC (PowerShell)

**Give the user only these substeps. Wait for **done** between major steps if they are overwhelmed.**

### A1. Open PowerShell

- Press the **Windows** key
- Type **PowerShell**
- Click **Windows PowerShell**

A window with text and a blinking cursor opens. That is normal.

### A2. Go to the project folder

Tell the user to **copy and paste** this line, then press **Enter**:

```powershell
cd d:\aamlpproj
```

Nothing exciting should happen — that is OK.

### A3. Run the packager

**Normal update** (CSS, pages, small code changes — most of the time):

```powershell
.\scripts\Build-DeployRelease.ps1 -OpenFolder
```

**Heavy update** (only if `composer.json` changed, or live site is 500 / “class not found”):

```powershell
.\scripts\Build-DeployRelease.ps1 -IncludeVendor -OpenFolder
```

This can take several minutes with `-IncludeVendor`.

### A4. If Windows says it will not run the script

Paste this **once**, press **Enter**, then run A3 again:

```powershell
Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
```

### A5. When it finishes

- The window should say **Done.**
- **File Explorer** opens a folder like: `d:\aamlpproj\deploy\out\2026-05-27_123456\`
- Inside you will see:
  - **`laravel`** — upload to server folder **`laravel`**
  - **`public_html`** — upload to server folder **`public_html`**
  - **`UPLOAD_INSTRUCTIONS.txt`** — same steps in a text file
  - Sometimes **`release.zip`** — optional: upload to `/home/minilp` and extract in cPanel

---

## STEP-BY-STEP — Upload with FileZilla

### B1. Connect FileZilla

- Host: `ftp.minilicenseplates.com`
- User: `chuck@minilicenseplates.com`
- Password: (your usual FTP password)

### B2. Upload `laravel`

1. **Left (PC):** open the `laravel` folder inside `deploy\out\<latest>\`
2. **Right (server):** open **`laravel`**
3. Select **all files and folders inside** the PC `laravel` folder (not the parent `out` folder)
4. Drag to the right
5. Choose **Overwrite** for all
6. Wait until the queue is **completely empty**

**Never upload `.env` from your PC** — the server keeps its own.

**Do not upload `plates/` from your PC** unless you added new photos locally (photos already live on the server).

### B3. Upload `public_html`

1. **Left (PC):** open the `public_html` folder from the same `deploy\out\<latest>\` folder
2. **Right (server):** open **`public_html`**
3. Select all inside the PC folder, drag over, overwrite, wait until finished

This includes **`index.php`** — it must keep pointing at `../laravel`.

### B4. Optional: one zip instead of thousands of files

1. Upload **`release.zip`** from `deploy\out\<latest>\` to **`/home/minilp`** in cPanel File Manager
2. **Extract** there — you should get updated **`laravel`** and **`public_html`** folders
3. Overwrite when asked

---

## STEP-BY-STEP — Clear cache on server (FileZilla)

After every upload, on the **server**:

1. Go to **`laravel/bootstrap/cache/`** — delete any **`.php`** files (keep `.gitignore` if present)
2. Go to **`laravel/storage/framework/views/`** — delete all **`.php`** files

No command line needed.

---

## STEP-BY-STEP — Test

1. Open https://minilicenseplates.com
2. Press **Ctrl+F5** (hard refresh)
3. Click the pages you changed

If you see **500 error**: download `laravel/storage/logs/laravel.log` (last 40 lines) or ask for help. Often fix: re-run packager with **`-IncludeVendor`**.

---

## Profile photo “Choose file” not working

Usually two things:

1. **Big “Choose photo…” button** — needs newer `profile/edit` view and `main.css` on the server (deploy `laravel` + `public_html` from the packager).
2. **Storage link missing** — photos save on the server but the site looks for them at `https://minilicenseplates.com/storage/...` which needs a folder link.

**Fix storage (one time):**

1. Upload `deploy/setup-storage-link.php` to **`public_html`**
2. Open https://minilicenseplates.com/setup-storage-link.php
3. It should say **SUCCESS** or **already exists**
4. **Delete** `setup-storage-link.php` from the server
5. Try profile photo again: pick file → **Save profile**

---

## Sign-in not working on the live site

**First — what do you see after you click Sign in?**

| What you see | Likely cause |
|--------------|----------------|
| Red text: **Username or password is incorrect** | No account on the **live** database (only on your PC), or wrong password |
| Red text: **account has been suspended** | Admin blocked the account |
| **419** or “Page expired” | Session/cookie problem — tell the assistant |
| **500** error | Database missing columns — migrations not run on server |
| Back at login, **no red message** | Session not saving — migrations or server `.env` |

**Most common fix:** Accounts you create on **http://localhost:8000** are **not** on the live site. Open https://minilicenseplates.com/register and **create an account there** (or use the same username/password you want on live).

**Check the server (one-time):** Upload `deploy/login-check.php` to `public_html`, open https://minilicenseplates.com/login-check.php, copy the text, paste to the assistant, then **delete** that file.

**If login-check says username column MISSING:** Ask your host to run once:
`php /home/minilp/laravel/artisan migrate --force`

---

## If the live site breaks badly

**Undo index.php (emergency):**  
`public_html/index.php` must contain:

```php
$laravelRoot = dirname(__DIR__) . '/laravel';
```

(not `new.minilicenseplates.com`, not `../vendor` alone)

**Diagnostic file (optional):** upload `deploy/site-check.php` to `public_html`, open https://minilicenseplates.com/site-check.php, copy the text, then **delete** that file from the server.

---

## For AI agents — how to help this user

1. **Assume no prior knowledge** of PowerShell, Laravel, or deploy scripts.
2. **One step per message** unless the user asks for the full list.
3. Use **FileZilla left/right**, **copy/paste**, **done** — not “run the packager” without the full `cd` + script lines.
4. Server paths: **`/laravel/`** and **`/public_html/`** — never **`new.minilicenseplates.com`**.
5. Technical reference for agents: `docs/DEPLOY_EASY.md`, `deploy/deploy.config.json`, `scripts/Build-DeployRelease.ps1`.
6. One-time server rename (already done if user said “works”): `docs/ONE_TIME_SERVER_LAYOUT.html`.

---

*Last updated: May 2026 — after rename to `laravel` + `public_html` layout.*

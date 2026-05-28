# Google AdSense — setup for MiniLicensePlates.com

This site has **three ad placements** wired in the code. Ads stay **off** until you turn them on in `.env` and Google approves your account.

## Where ads appear

| Placement | Location | Best ad type in AdSense |
|-----------|----------|-------------------------|
| **below-header** | Under the nav bar, above page content (every public page) | Display — **Responsive** or horizontal banner |
| **above-footer** | Above the footer links (every public page) | Display — **Responsive** |
| **search-mid** | Catalog search results, after the 4th result card | Display — **Responsive** |

**Not shown on:** Admin pages, login/register (same layout but you may want to keep ads off member-only pages later).

**Not shown on:** History timeline modal (no ads inside popups).

Locally you will see **gray dashed placeholders** until real ads are enabled (so you can check layout without clicking ads).

---

## Step 1 — Apply for AdSense

1. Go to [https://adsense.google.com](https://adsense.google.com) and sign in with the Google account you want to use for payouts.
2. Click **Get started** and add your site: **https://minilicenseplates.com** (use the live URL, not localhost).
3. Google will ask you to prove you own the site. Common options:
   - **HTML tag** in the page `<head>` — we load the AdSense script from `ADSENSE_CLIENT` once you have an ID; during review they may give a one-time verification snippet (paste into `resources/views/components/seo-head.blade.php` temporarily if they require it).
   - **ads.txt** — after you set `ADSENSE_CLIENT` in production `.env`, open **https://minilicenseplates.com/ads.txt** and confirm it shows one line with your `pub-` ID.
4. Complete tax and payment details when prompted.
5. Wait for **approval** (often a few days to a few weeks). Until approved, ad units may stay empty.

**Before you apply:** Google expects real content, clear navigation, and a **privacy policy** that mentions cookies and third-party ads. Your footer still has placeholder “Terms of Service” links — consider publishing a simple Privacy Policy page and linking it in the footer before or during review.

---

## Step 2 — Get your publisher ID

After account setup (or when approved):

1. AdSense → **Account** → **Account information**.
2. Copy **Publisher ID** — looks like `ca-pub-1234567890123456`.

On the **live server** `laravel/.env`:

```env
ADSENSE_ENABLED=true
ADSENSE_CLIENT=ca-pub-1234567890123456
```

(Use your real ID, not this example.)

Clear config cache on the server after editing `.env`:

```text
Delete laravel/bootstrap/cache/config.php
```

(or run `php artisan config:clear` on the server if you have SSH.)

---

## Step 3 — Create ad units (one per placement)

1. AdSense → **Ads** → **By ad unit** → **Display ads**.
2. Create **three** responsive display units. Suggested names:

   - `MLP - Below header`
   - `MLP - Above footer`
   - `MLP - Search mid`

3. For each unit, open it and copy the **data-ad-slot** number (digits only, e.g. `1234567890`).

4. Add to **live** `.env`:

```env
ADSENSE_SLOT_BELOW_HEADER=1111111111
ADSENSE_SLOT_ABOVE_FOOTER=2222222222
ADSENSE_SLOT_SEARCH_MID=3333333333
```

5. Upload updated `.env` (never commit `.env` to GitHub).

---

## Step 4 — Deploy code and assets

Upload with your normal deploy (`docs/DEPLOY_PLAIN_ENGLISH.md`):

- `laravel/config/adsense.php`
- `laravel/app/Support/AdSense.php`
- `laravel/resources/views/components/adsense-head.blade.php`
- `laravel/resources/views/components/adsense-slot.blade.php`
- `laravel/resources/views/layouts/app.blade.php`
- `laravel/resources/views/components/footer.blade.php`
- `laravel/resources/views/search.blade.php`
- `laravel/routes/web.php`
- `public_html/main.css`

---

## Step 5 — Verify on the live site

1. Open **https://minilicenseplates.com/ads.txt** — must show  
   `google.com, pub-XXXXXXXX, DIRECT, f08c47fec0942fa0`
2. View home, gallery, and a search with results — ads or AdSense placeholders should appear in the two site-wide slots; search should show the mid-grid slot after several results.
3. In AdSense, check **Sites** for any policy messages.

**Do not click your own ads** repeatedly — Google can flag invalid traffic.

---

## Local development

Default `.env` keeps ads off on localhost:

```env
ADSENSE_ENABLED=false
ADSENSE_SHOW_PLACEHOLDERS=true
```

To preview **real** ads locally (optional):

```env
ADSENSE_ENABLED=true
ADSENSE_CLIENT=ca-pub-...
ADSENSE_SLOT_BELOW_HEADER=...
ADSENSE_SHOW_ON_LOCAL=true
```

---

## Optional toggles

| Variable | Default | Purpose |
|----------|---------|---------|
| `ADSENSE_SHOW_PLACEHOLDERS` | `true` | Gray “Ad placement: …” boxes when ads are not live |
| `ADSENSE_SHOW_ON_LOCAL` | `false` | Allow real AdSense script on localhost |

---

## If ads stay blank after approval

- Wait 24–48 hours after creating units.
- Confirm `ADSENSE_ENABLED=true` and all three slot IDs on **production** `.env`.
- Disable ad blockers when testing.
- Check AdSense **Policy center** for site-level issues.

For code changes (extra placements, hide ads on profile/collection pages), say which pages should stay ad-free.

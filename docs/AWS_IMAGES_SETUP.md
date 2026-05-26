# AWS Image Hosting Setup – MiniLicensePlates.com

**Status:** In progress — blocked on AWS account verification for CloudFront (support case submitted).  
**Last updated:** 2026-05-24  
**Purpose:** Host plate images on S3 + CloudFront instead of copying `public/plates/` to the cPanel VPS on every deploy.

When the user returns after AWS responds, read this file first and continue from **§8 Next steps when CloudFront is enabled**.

---

## 1. Why we are doing this

- Plate images live in `public/plates/{set_code}/` (~1,400 files, ~500 MB locally).
- That folder is **gitignored** and must be copied manually to production (FileZilla).
- Laravel app and MySQL stay on the existing cPanel VPS; **only images** move to AWS.
- Future CMS uploads can write directly to S3.

**Target URL pattern:**

```
https://images.minilicenseplates.com/plates/{set_code}/{image_base}_a.{ext}
```

Example: `https://images.minilicenseplates.com/plates/m88p/AL_a.jpg`

---

## 2. Architecture (planned)

```
Browser → CloudFront CDN → S3 bucket (private)
Browser → Laravel on cPanel VPS → MySQL
Laravel / future CMS → S3 (upload via IAM credentials)
```

| AWS service | Role |
|-------------|------|
| **S3** | Store `plates/{set_code}/...` (same paths as today) |
| **CloudFront** | CDN, HTTPS, cache; only way public reads S3 |
| **ACM** | Free SSL cert for `images.minilicenseplates.com` (must be in **us-east-1**) |
| **IAM user** | Laravel app credentials for read/write to `plates/*` prefix |

**DNS:** cPanel Zone Editor (not Route 53). Two CNAME records needed later:
1. ACM validation CNAME (temporary, for cert issuance)
2. `images` → CloudFront distribution domain

---

## 3. AWS account & region

- User is signed into AWS Console; new to AWS.
- **Always use region:** **US East (N. Virginia) `us-east-1`** for S3, ACM (CloudFront certs), and CloudFront setup.
- DNS for `minilicenseplates.com` is managed in **cPanel**, not Route 53.

---

## 4. Completed steps

### S3 bucket — DONE

| Setting | Value |
|---------|--------|
| Bucket name | `minilicenseplates-images-prod` |
| Region | `us-east-1` |
| Block Public Access | All four options **ON** (private bucket) |
| Encryption | SSE-S3 enabled |
| Versioning | Disabled |

### CloudFront — BLOCKED

User reached **Review and create** with correct settings but **Create distribution failed** with:

> Your account must be verified before you can add new CloudFront resources. To verify your account, please contact AWS Support and include this error message.

**Action taken:** User instructed to open AWS Support case:
- **Case type:** Account and billing support (not Technical)
- **Subject:** Request CloudFront access for new account
- **Include:** Account ID, use case (S3 CDN for minilicenseplates.com plate images, low traffic hobby site)

### CloudFront settings to use when approved (already chosen, recreate same config)

| Setting | Value |
|---------|--------|
| Distribution name | `minilicenseplates-images` |
| Description | `Plate images for MiniLicensePlates.com` |
| Distribution type | Single website or app |
| Route 53 domain | **Leave blank** (cPanel DNS) |
| Origin | S3: `minilicenseplates-images-prod.s3.us-east-1.amazonaws.com` |
| Origin path | **Empty** (blank) — URLs keep `/plates/...` prefix |
| Grant CloudFront access to origin | **Yes** (OAC + bucket policy) |
| Origin Shield | No |
| Cache | Default S3-optimized settings (OK for v1) |

**Why origin path is blank:** Laravel today uses `asset('plates/' . $set_code . '/' . ...)`. S3 keys must be `plates/m88p/AL_a.jpg` and CloudFront URLs `/plates/m88p/AL_a.jpg`.

---

## 5. Not started yet (do after CloudFront works)

### Phase A — Test CloudFront

1. Create distribution (same settings as §4).
2. Wait until Status = **Enabled**; copy **Distribution domain name** (`dxxxx.cloudfront.net`).
3. S3 → bucket → folder `plates/` → upload `plates/test.jpg`.
4. Test: `https://dxxxx.cloudfront.net/plates/test.jpg`

### Phase B — SSL + custom domain

1. **Certificate Manager** (region **us-east-1**) → Request public cert for `images.minilicenseplates.com` → DNS validation.
2. cPanel → add ACM validation CNAME → wait until cert status = **Issued**.
3. CloudFront → Edit distribution → Alternate domain name: `images.minilicenseplates.com` → select ACM cert.
4. cPanel → CNAME `images` → `dxxxx.cloudfront.net`.
5. Test: `https://images.minilicenseplates.com/plates/test.jpg`

### Phase C — Bulk upload all plates

Local path: `d:\aamlpproj\public\plates\` (~1,386 files, ~499 MB).

S3 structure must mirror local:

```
plates/
  m88p/
    AL_a.jpg
    AL_b.jpg
  c36g/
    ...
```

**Recommended:** AWS CLI on Windows:

```powershell
aws s3 sync "d:\aamlpproj\public\plates" "s3://minilicenseplates-images-prod/plates" --dryrun
# remove --dryrun when ready
aws s3 sync "d:\aamlpproj\public\plates" "s3://minilicenseplates-images-prod/plates"
```

Console upload OK for one test file only; not for full library.

### Phase D — IAM user for Laravel

1. IAM → Users → `minilp-plates-app` → programmatic access key.
2. Policy scoped to bucket `minilicenseplates-images-prod`, prefix `plates/*` (ListBucket + Get/Put/DeleteObject).
3. Store keys in production `.env` only — never commit.

### Phase E — Laravel code changes (not implemented yet)

Centralize plate URLs; switch from `asset('plates/...')` to env-driven CDN base URL.

**Files to change:**

| File | Current behavior |
|------|------------------|
| `app/Models/Plate.php` | `frontImageUrl()` / `backImageUrl()` use `asset('plates/...')` |
| `app/Http/Controllers/GalleryController.php` | `collectSetImages()` uses `scandir()` on `public/plates/` |
| `resources/views/components/gallery-result-card.blade.php` | placeholder via `asset('plate_missing.png')` |
| `resources/views/gallery/index.blade.php` | onerror fallback to local placeholder |

**Planned env vars:**

```env
PLATES_DISK=local          # dev
PLATES_DISK=s3             # production (optional, for uploads)
PLATES_URL=https://images.minilicenseplates.com
# When using S3 API from Laravel:
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=minilicenseplates-images-prod
```

**Gallery refactor:** Replace filesystem scan with DB-backed image list (`image_base`, `image_ext`, `has_back_image`) for gallery show pages.

`config/filesystems.php` already has an `s3` disk stub; add dedicated `plates` disk when implementing.

---

## 6. What stays on the VPS (not AWS)

- Laravel app, MySQL, CSS, layout images
- `public/plate_missing.png` (small fallback; can stay local)
- `home_top_banner.jpg`, `brands.jpg`, etc.

---

## 7. Rough cost estimate

~$2–10/month for this site size (S3 storage ~500 MB + low CloudFront traffic).

---

## 8. Next steps when CloudFront is enabled

**User message to agent:** “AWS approved CloudFront” (or paste support reply).

1. Recreate CloudFront distribution using settings in §4.
2. Confirm test image loads via `dxxxx.cloudfront.net`.
3. Complete ACM + cPanel DNS (Phase B).
4. Bulk sync `public/plates/` to S3 (Phase C).
5. Create IAM user and keys (Phase D).
6. Implement Laravel URL helper + gallery DB refactor (Phase E).
7. Deploy code + `.env` to production; verify gallery and search images.
8. Optional: remove `public/plates/` from VPS after backup.

---

## 9. Common errors reference

| Error | Cause | Fix |
|-------|--------|-----|
| Account must be verified for CloudFront | New AWS account | Support case (Account and billing) |
| 403 from CloudFront | Bucket policy missing | Re-enable “Grant CloudFront access”; update bucket policy |
| 404 from CloudFront | Wrong S3 key | Key must include `plates/` prefix |
| ACM cert not in dropdown | Wrong region | Request cert in **us-east-1** only |
| Cert pending validation | DNS CNAME wrong | Match ACM validation record exactly in cPanel |
| Custom domain SSL error | DNS not propagated | Wait; verify `images` CNAME → CloudFront |

---

## 10. Support case text (for reference)

```
I am setting up CloudFront to serve static images for my website 
minilicenseplates.com (a collector reference site). When I try to 
create a CloudFront distribution I receive:

"Your account must be verified before you can add new CloudFront 
resources."

Please verify my account and enable CloudFront for my account.

Use case: CDN for S3-hosted plate images (~500MB, low traffic 
hobby/collectibles site)
Region: us-east-1
S3 bucket: minilicenseplates-images-prod
```

---

## 11. Related project docs

- `PROJECT_REFERENCE.md` — app setup; plate images currently `public/plates/{setCode}/`
- `GALLERY_CATALOG_SPEC.md` — `image_base`, `image_ext`, `has_back_image` schema
- `AGENTS.md` — lists this file for agent context

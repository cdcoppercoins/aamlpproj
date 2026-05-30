# History timeline — adding your paragraphs and images

The History page (`/history`) shows a timeline. Each button opens a modal with a **title**, **paragraph**, and optional **image**.

## Where to edit text

**Preferred:** sign in as admin → **History timeline** in the admin menu (`/admin/history-timeline`).

**Legacy fallback:** `config/history_timeline.php` on your PC (used only when the database table is empty).

Copy one block per paragraph from your document. Example:

```php
[
    'id' => '1953-wheaties',
    'label' => '1953',
    'title' => 'Wheaties mail-away plates',
    'body' => '<p>Your full paragraph here.</p><p>Second paragraph if needed.</p>',
    'image' => 'history-media/1953-wheaties.jpg',
    'alt' => 'Short description of the photo for screen readers',
    'caption' => 'Caption shown in italics under the photo in the modal',
],
```

| Field | Purpose |
|-------|---------|
| `id` | Unique slug (no spaces) — `1953-wheaties` |
| `label` | Text on the timeline button — usually a year |
| `title` | Headline inside the modal |
| `body` | Your paragraph(s). Plain text works; use `<p>...</p>` for multiple paragraphs |
| `image` | File in `public/history-media/` or `null` if no photo |
| `alt` | Image description (accessibility) |
| `caption` | Line under the photo in the modal (italic). Omit or `null` if none |

Add as many entries as you need (two dozen is fine).

## Where to put images

1. Save photos as JPG, PNG, or WebP in **`public/history-media/`** (not `public/history/` — that breaks the `/history` page locally)
2. Use the same filename as in the config (`history-media/my-photo.jpg` means `public/history-media/my-photo.jpg`)

## Test locally

```powershell
cd d:\aamlpproj
php artisan serve
```

Open http://localhost:8000/history — hover a year (or tap on phone).

## Deploy to live site

Upload with FileZilla (or the deploy packager):

- `config/history_timeline.php` → `laravel/config/`
- `app/Http/Controllers/HistoryController.php` → `laravel/app/Http/Controllers/`
- `resources/views/history.blade.php` → `laravel/resources/views/`
- `routes/web.php` → `laravel/routes/`
- `public/main.css` → `public_html/`
- `public/history-media/` (your images) → `public_html/history-media/`

Clear `laravel/bootstrap/cache/*.php` on the server after uploading config.

## Sample entries

The config file ships with **placeholder** entries (1930s, 1953, etc.). Replace their `body` text with yours and swap `image` paths when your photos are ready.

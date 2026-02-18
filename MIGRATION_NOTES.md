# MiniLicensePlates.com - Laravel Migration Notes

## Site Overview
A visual reference library for miniature license plate toys issued with candy, gum, and cereal. The site displays plates in a gallery format with hover effects to show front/back sides.

## Current Site Structure (mlp_code folder)

### Main Pages
- **index.php** - Homepage with welcome message and links
- **gallery.php** - Main gallery page (shows sets, then individual plates)
- **about.php** - About page with collector info
- **history.php** - History page (placeholder, "Coming soon")
- **contribute.php** - Contact/contribution form with email functionality

### Include Files
- **header.php** - Site header with banner image and navigation
- **footer.php** - Site footer with copyright
- **inc/page_top.php** - HTML head section, includes header
- **inc/page_bottom.php** - Closes content wrapper, includes footer
- **inc/modal_script.php** - JavaScript for image modal popups

### Assets
- **main.css** - Main stylesheet (uses Nunito font, background image)
- **header_banner.png** - Header banner image
- **background_img.jpg** - Page background image

### Data Structure
- **plates/** folder - Contains subfolders for each set (e.g., `c36g`, `m88p`, etc.)
  - Images named with pattern: `{name}_a.{ext}` (front) and `{name}_b.{ext}` (back)
  - Gallery scans for `*_a.*` files and pairs them with `*_b.*` files
- **setinfo/** folder - Contains metadata files:
  - `{code}_info.php` - Set information displayed above gallery
  - `{code}_varieties.php` - Varieties information displayed below gallery

### Gallery System
- Uses `$folderMap` array mapping set names to folder codes
- Checks for available sets by scanning for `*a.*` image files
- Displays thumbnails, then full gallery when set is selected
- Hover effect swaps `_a` image with `_b` image
- Click opens modal with larger image

### Navigation
- HOME, GALLERY, ABOUT, HISTORY, CONTRIBUTE, SHOP (external eBay link)

### Contact Form
- Sends email to: cdcoppercoins@gmail.com
- Uses honeypot field (`company`) for spam protection
- Form action points to `contribute_test.php` (may need updating)

### Key Features to Preserve
1. Gallery browsing with set selection
2. Image hover effect (front/back flip)
3. Modal popup for larger images
4. Responsive design
5. Set information displays
6. Contact form functionality

## Migration Plan

### Phase 1: Basic Structure
1. Create Blade layout files (header, footer, master layout)
2. Convert main pages to Blade views
3. Set up routes
4. Move CSS/assets to Laravel public folder

### Phase 2: Gallery System
1. Create Gallery controller
2. Convert gallery.php logic to controller methods
3. Move plate images to Laravel public folder
4. Convert setinfo includes to Blade components or data files

### Phase 3: Contact Form
1. Create Contact controller
2. Convert form to Laravel form handling
3. Set up email sending (Laravel Mail)

### Phase 4: Polish
1. Update navigation links
2. Ensure all assets load correctly
3. Test all functionality
4. Clean up old files

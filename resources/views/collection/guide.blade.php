@extends('layouts.app')

@section('title', 'How to Use My Collection | MiniLicensePlates.com')

@section('meta_description', 'Step-by-step help for tracking miniature license plates in your member collection — sets, want list, PDF checklists, reports, and public sharing.')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page collection-guide-page">
    <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
        <ol class="gallery-breadcrumbs-list">
            <li><a href="{{ route('collection.index') }}">My Collection</a></li>
            <li aria-current="page">How to use</li>
        </ol>
    </nav>

    <header class="collection-guide-header">
        <h1 class="home-title">How to use My Collection</h1>
        <p class="home-lead">
            Record what you own, mark gaps on your want list, print checklists for shows, and optionally share a set with other signed-in members.
            Expand each section below for step-by-step instructions and sample screenshots of each screen.
        </p>
        <p class="collection-guide-toolbar">
            <a class="home-primary-btn" href="{{ route('collection.index') }}">Back to My Collection</a>
            <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.manage') }}">Add or edit a set</a>
        </p>
    </header>

    <section class="collection-reports-list collection-guide-sections" aria-label="Collection help topics">
        <ul class="collection-reports-accordion">
            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="true"
                        aria-controls="collection-guide-overview">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">1. My Collection home page</span>
                </button>
                <div id="collection-guide-overview"
                     class="collection-reports-accordion-panel">
                    <p>The <strong>My Collection</strong> page is your dashboard. The headline shows how many sets you have entered, how many physical items you own, want-list count, and a private catalog value total (only you see dollar amounts).</p>
                    @include('components.collection-guide-screenshot', [
                        'file' => 'index-overview.svg',
                        'alt' => 'My Collection home page with set list and action buttons',
                        'caption' => 'Summary counts, shortcuts, and your set cards.',
                    ])
                    <h3 class="collection-guide-subtitle">Toolbar buttons</h3>
                    <ul class="collection-guide-list">
                        <li><strong>Add or edit a set</strong> — open the full-set grid for one catalog run (best for completing a whole set).</li>
                        <li><strong>Reports</strong> — printable have/missing, partial-set gaps, and want-list tables.</li>
                        <li><strong>Catalog search</strong> — find one plate and add it without opening the whole set.</li>
                        <li><strong>Profile</strong> — name, email, and optional photo shown on public collection pages.</li>
                    </ul>
                    <h3 class="collection-guide-subtitle">Your sets list</h3>
                    <p>Each card is a catalog set where you have at least one row (owned items or want list). Use <strong>Edit set</strong> or the set title to open the grid. <strong>PDF</strong> downloads your recorded rows for that set. <strong>Visibility</strong> controls whether other members can view that set (see section 7).</p>
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-guide-add">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">2. Adding plates to your collection</span>
                </button>
                <div id="collection-guide-add"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <h3 class="collection-guide-subtitle">Option A — one plate from Catalog Search</h3>
                    <p>Sign in, run a search, and use the buttons on each result card:</p>
                    @include('components.collection-guide-screenshot', [
                        'file' => 'catalog-search-add.svg',
                        'alt' => 'Add item and Want list buttons on a search result',
                        'caption' => 'Choose a grade (optional), then Add item or Want list.',
                    ])
                    <ul class="collection-guide-list">
                        <li><strong>Add item</strong> — creates one owned copy with the grade you selected (if any).</li>
                        <li><strong>Want list</strong> — marks the catalog slot as wanted, not owned.</li>
                        <li><strong>Edit/Add</strong> — appears after the plate is in your collection; opens the detailed editor for multiple copies, dates, and per-item notes.</li>
                    </ul>
                    <h3 class="collection-guide-subtitle">Option B — whole set at once</h3>
                    <p>From My Collection, choose <strong>Add or edit a set</strong>, pick the set from the dropdown, and work down the table (section 3). Use <strong>Quick fill defaults</strong> at the top when you own a complete run with the same grade and storage for every empty row.</p>
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-guide-manage">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">3. Edit collection by set (the main grid)</span>
                </button>
                <div id="collection-guide-manage"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <p>After you select a catalog set, every jurisdiction/variety in that set appears in one table.</p>
                    @include('components.collection-guide-screenshot', [
                        'file' => 'manage-set-table.svg',
                        'alt' => 'Edit by set table with jurisdiction, items, want, storage, and save row',
                        'caption' => 'Example rows: owned (green), empty, and want list (peach).',
                        'wide' => true,
                    ])
                    <h3 class="collection-guide-subtitle">Columns</h3>
                    <ul class="collection-guide-list">
                        <li><strong>Items</strong> — how many you own and each copy&rsquo;s grade (use <strong>Add item</strong> in the cell for more than one). Serial numbers are assigned automatically when saved.</li>
                        <li><strong>Value</strong> — private catalog total for that row from the pricing guide at your grades.</li>
                        <li><strong>Want</strong> — check when you are looking for the plate but do not own it. Leave items empty and uncheck Want to clear the row.</li>
                        <li><strong>Storage</strong> — where you keep the plate (binder, box, etc.). Applies to all copies on that row unless you edit per item on the single-plate editor.</li>
                        <li><strong>Notes</strong> — private row notes (not shown on public member views).</li>
                    </ul>
                    <h3 class="collection-guide-subtitle">Quick fill</h3>
                    <p>Set <strong>items per plate</strong>, default grade, storage, and notes, then <strong>Fill set &amp; save</strong> to apply to empty rows or the entire set. <strong>Apply to form (preview)</strong> fills the page without saving so you can review first.</p>
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-guide-save">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">4. Saving your work</span>
                </button>
                <div id="collection-guide-save"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <p>Changes are not stored until you save.</p>
                    <ul class="collection-guide-list">
                        <li><strong>Save row</strong> — saves only that plate&rsquo;s line (fast when you update one state).</li>
                        <li><strong>Save entire set</strong> — saves every row on the page; use this before leaving or downloading a PDF.</li>
                    </ul>
                    @include('components.collection-guide-screenshot', [
                        'file' => 'save-entire-set.svg',
                        'alt' => 'Sticky bar at bottom with Save entire set button',
                        'caption' => 'The save bar stays visible while you scroll.',
                        'wide' => true,
                    ])
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-guide-edit-one">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">5. Edit a single catalog entry (detail screen)</span>
                </button>
                <div id="collection-guide-edit-one"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <p>From Catalog Search, click <strong>Edit/Add</strong> on a plate you already track. This screen is best when you need different grades, acquisition dates, prices, storage, or notes <em>per physical copy</em>.</p>
                    <ul class="collection-guide-list">
                        <li>Each <strong>Item</strong> block is one owned copy. Serial numbers are permanent and cannot be edited.</li>
                        <li><strong>Entry notes</strong> apply to the catalog row (same as the Notes column on the set grid).</li>
                        <li>Check <strong>On my want list</strong> to move the row to wanted-only; owned copies are removed when you save.</li>
                    </ul>
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-guide-pdf">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">6. PDF checklists</span>
                </button>
                <div id="collection-guide-pdf"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <p>On the set grid or from a set card on My Collection, use the PDF links after you save.</p>
                    @include('components.collection-guide-screenshot', [
                        'file' => 'pdf-checklist.svg',
                        'alt' => 'Download PDF checklist and Download my entries buttons',
                        'caption' => 'Save the set first so the PDF includes your latest grades and locations.',
                    ])
                    <ul class="collection-guide-list">
                        <li><strong>Download PDF checklist</strong> — every catalog slot in the set; your grades, quantities, want marks, storage, and private notes appear where you have entered them.</li>
                        <li><strong>Download my entries (PDF)</strong> — only rows you have recorded (smaller file for what you actually track).</li>
                    </ul>
                    <p>Catalog dollar values on the PDF are private, like on the website.</p>
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-guide-reports">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">7. Reports</span>
                </button>
                <div id="collection-guide-reports"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <p>Open <strong>Reports</strong> from My Collection. Each report type expands on that page; pick filters and click <strong>View report</strong>, then print from your browser.</p>
                    <ul class="collection-guide-list">
                        <li><strong>Set inventory</strong> — full set in catalog order: Have, Missing, or Want per slot, with serials and grades.</li>
                        <li><strong>Missing items</strong> — gaps only from sets where you already own at least one plate (good for finishing partial runs).</li>
                        <li><strong>Want list</strong> — everything you marked wanted, filterable by set, decade, or jurisdiction. Printable checklist with checkboxes.</li>
                    </ul>
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-guide-public">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">8. Public sharing and other members</span>
                </button>
                <div id="collection-guide-public"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <p>By default your sets are <strong>Private</strong>. Change <strong>Visibility</strong> to <strong>Public</strong> on a set card to let other signed-in members view that set&rsquo;s grid (not your private row notes).</p>
                    @include('components.collection-guide-screenshot', [
                        'file' => 'public-visibility.svg',
                        'alt' => 'Visibility dropdown set to Public on a set card',
                        'caption' => 'Public views include jurisdiction, quantity, grade, and storage you entered.',
                    ])
                    <p>The <strong>Public member collections</strong> section on My Collection lists collectors who shared at least one set. You can open their profile and browse each public set in a read-only table.</p>
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-guide-tips">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">9. Tips and troubleshooting</span>
                </button>
                <div id="collection-guide-tips"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <ul class="collection-guide-list">
                        <li>If a PDF shows old data, return to the set grid, click <strong>Save entire set</strong>, then download again.</li>
                        <li>Storage on the set grid applies to all copies on that row; use the single-plate editor for different storage per copy.</li>
                        <li>Want list rows do not count as owned in totals until you add items and uncheck Want.</li>
                        <li>Username is fixed; update your display name and photo under <strong>Profile</strong>.</li>
                    </ul>
                    <p>Questions or a bug? Use <a href="{{ route('contribute') }}">Contribute / contact</a> and mention which set and page you were on.</p>
                </div>
            </li>
        </ul>
    </section>
</div>
@endsection

@push('scripts')
    @include('components.collection-accordion-script')
@endpush

@extends('layouts.app')

@section('title', 'Collection Reports | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page collection-reports-page">
    <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
        <ol class="gallery-breadcrumbs-list">
            <li><a href="{{ route('collection.index') }}">My Collection</a></li>
            <li aria-current="page">Reports</li>
        </ol>
    </nav>

    <h1 class="home-title">Collection reports</h1>
    <p class="home-lead">View and print reports from your collection data.</p>

    @if (session('error'))
        <p class="collection-report-flash" role="alert">{{ session('error') }}</p>
    @endif

    <section class="collection-reports-list" aria-label="Available reports">
        <ul class="collection-reports-accordion">
            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-report-set-inventory">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">Set inventory (have / missing)</span>
                </button>
                <div id="collection-report-set-inventory"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <p class="collection-report-card-desc">
                        Full catalog list for one set in order. Shows each catalog slot as
                        <strong>Have</strong> (you own at least one item),
                        <strong>Missing</strong> (no owned items yet), or
                        <strong>Want</strong> (on your want list). Includes item serials and grades where recorded.
                    </p>
                    <form class="collection-report-form" method="get" action="{{ route('collection.reports.set-inventory') }}">
                        <label class="collection-report-field">
                            <span class="collection-report-label">Set</span>
                            <select name="set_name" class="collection-report-select" required>
                                <option value="">Choose a set…</option>
                                @foreach ($setNames as $set)
                                    <option value="{{ $set->set_name }}" @selected(old('set_name') === $set->set_name && ! in_array(old('report_type'), ['missing', 'want'], true))>
                                        {{ $set->set_name }}
                                        @if ($set->year) ({{ $set->year }}) @endif
                                        — {{ number_format($set->plate_count) }} catalog slots
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <p class="collection-report-actions">
                            <button type="submit" class="home-primary-btn">View report</button>
                        </p>
                    </form>
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-report-missing-items">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">Missing items (partial sets)</span>
                </button>
                <div id="collection-report-missing-items"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <p class="collection-report-card-desc">
                        Lists catalog slots you do <strong>not</strong> own yet, but only from sets where you already
                        own at least one item. Filter by all qualifying sets, decade, one set, or jurisdiction.
                        Sort by column on the report page.
                    </p>
                    @if ($reportSets->isEmpty())
                        <p class="collection-report-card-desc">Record at least one owned item in a set to use this report.</p>
                    @else
                        <form class="collection-report-form" method="get" action="{{ route('collection.reports.missing-items') }}" data-report-scope>
                            <input type="hidden" name="report_type" value="missing">
                            <fieldset class="collection-report-scope-fieldset">
                                <legend class="collection-report-label">Include missing items from</legend>
                                <ul class="collection-report-scope-options">
                                    <li>
                                        <label class="collection-report-scope-option">
                                            <input type="radio" name="scope" value="all" @checked(old('report_type', 'missing') === 'missing' && old('scope', 'all') === 'all')>
                                            <span>All qualifying sets</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label class="collection-report-scope-option">
                                            <input type="radio" name="scope" value="decade" @checked(old('report_type') === 'missing' && old('scope') === 'decade')>
                                            <span>Decade</span>
                                        </label>
                                        <div class="collection-report-scope-panel" data-report-scope-panel="decade" hidden>
                                            <select name="decade" class="collection-report-select">
                                                <option value="">Choose decade…</option>
                                                @foreach ($reportDecades as $decade)
                                                    <option value="{{ $decade }}" @selected(old('report_type') === 'missing' && (string) old('decade') === (string) $decade)>
                                                        {{ $decade }}s
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <label class="collection-report-scope-option">
                                            <input type="radio" name="scope" value="set" @checked(old('report_type') === 'missing' && old('scope') === 'set')>
                                            <span>Set</span>
                                        </label>
                                        <div class="collection-report-scope-panel" data-report-scope-panel="set" hidden>
                                            <select name="set_name" class="collection-report-select">
                                                <option value="">Choose set…</option>
                                                @foreach ($reportSets as $set)
                                                    <option value="{{ $set->set_name }}" @selected(old('report_type') === 'missing' && old('set_name') === $set->set_name && old('scope') === 'set')>
                                                        {{ $set->set_name }}
                                                        @if ($set->year) ({{ $set->year }}) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <label class="collection-report-scope-option">
                                            <input type="radio" name="scope" value="jurisdiction" @checked(old('report_type') === 'missing' && old('scope') === 'jurisdiction')>
                                            <span>Jurisdiction</span>
                                        </label>
                                        <div class="collection-report-scope-panel" data-report-scope-panel="jurisdiction" hidden>
                                            <select name="jurisdiction" class="collection-report-select">
                                                <option value="">Choose jurisdiction…</option>
                                                @foreach ($reportJurisdictions as $jurisdiction)
                                                    <option value="{{ $jurisdiction }}" @selected(old('report_type') === 'missing' && old('jurisdiction') === $jurisdiction)>
                                                        {{ strtoupper($jurisdiction) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </li>
                                </ul>
                            </fieldset>
                            <p class="collection-report-actions">
                                <button type="submit" class="home-primary-btn">View report</button>
                            </p>
                        </form>
                    @endif
                </div>
            </li>

            <li class="collection-reports-accordion-item">
                <button type="button"
                        class="collection-reports-accordion-trigger"
                        aria-expanded="false"
                        aria-controls="collection-report-want-list">
                    <span class="collection-reports-accordion-arrow" aria-hidden="true"></span>
                    <span class="collection-reports-accordion-label">Want list</span>
                </button>
                <div id="collection-report-want-list"
                     class="collection-reports-accordion-panel"
                     hidden>
                    <p class="collection-report-card-desc">
                        Printable checklist of plates on your want list. Filter by all wanted plates, decade, one set,
                        or jurisdiction. Sort by column on the report page.
                    </p>
                    @if ($wantReportSets->isEmpty())
                        <p class="collection-report-card-desc">Mark at least one plate on your want list to use this report.</p>
                    @else
                        <form class="collection-report-form" method="get" action="{{ route('collection.reports.want-list') }}" data-report-scope>
                            <input type="hidden" name="report_type" value="want">
                            <fieldset class="collection-report-scope-fieldset">
                                <legend class="collection-report-label">Include want list items from</legend>
                                <ul class="collection-report-scope-options">
                                    <li>
                                        <label class="collection-report-scope-option">
                                            <input type="radio" name="scope" value="all" @checked(old('report_type') === 'want' && old('scope', 'all') === 'all')>
                                            <span>All want list items</span>
                                        </label>
                                    </li>
                                    <li>
                                        <label class="collection-report-scope-option">
                                            <input type="radio" name="scope" value="decade" @checked(old('report_type') === 'want' && old('scope') === 'decade')>
                                            <span>Decade</span>
                                        </label>
                                        <div class="collection-report-scope-panel" data-report-scope-panel="decade" hidden>
                                            <select name="decade" class="collection-report-select">
                                                <option value="">Choose decade…</option>
                                                @foreach ($wantReportDecades as $decade)
                                                    <option value="{{ $decade }}" @selected(old('report_type') === 'want' && (string) old('decade') === (string) $decade)>
                                                        {{ $decade }}s
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <label class="collection-report-scope-option">
                                            <input type="radio" name="scope" value="set" @checked(old('report_type') === 'want' && old('scope') === 'set')>
                                            <span>Set</span>
                                        </label>
                                        <div class="collection-report-scope-panel" data-report-scope-panel="set" hidden>
                                            <select name="set_name" class="collection-report-select">
                                                <option value="">Choose set…</option>
                                                @foreach ($wantReportSets as $set)
                                                    <option value="{{ $set->set_name }}" @selected(old('report_type') === 'want' && old('set_name') === $set->set_name && old('scope') === 'set')>
                                                        {{ $set->set_name }}
                                                        @if ($set->year) ({{ $set->year }}) @endif
                                                        — {{ number_format($set->plate_count) }} wanted
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </li>
                                    <li>
                                        <label class="collection-report-scope-option">
                                            <input type="radio" name="scope" value="jurisdiction" @checked(old('report_type') === 'want' && old('scope') === 'jurisdiction')>
                                            <span>Jurisdiction</span>
                                        </label>
                                        <div class="collection-report-scope-panel" data-report-scope-panel="jurisdiction" hidden>
                                            <select name="jurisdiction" class="collection-report-select">
                                                <option value="">Choose jurisdiction…</option>
                                                @foreach ($wantReportJurisdictions as $jurisdiction)
                                                    <option value="{{ $jurisdiction }}" @selected(old('report_type') === 'want' && old('jurisdiction') === $jurisdiction)>
                                                        {{ strtoupper($jurisdiction) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </li>
                                </ul>
                            </fieldset>
                            <p class="collection-report-actions">
                                <button type="submit" class="home-primary-btn">View report</button>
                            </p>
                        </form>
                    @endif
                </div>
            </li>
        </ul>
    </section>

    <p class="collection-reports-back">
        <a href="{{ route('collection.index') }}">← Back to my collection</a>
    </p>
</div>
@endsection

@push('scripts')
@include('components.collection-reports-sort-script')
@include('components.collection-accordion-script')
<script>
(function () {
    @if (old('report_type') === 'want')
    (function () {
        var trigger = document.querySelector('[aria-controls="collection-report-want-list"]');
        var panel = document.getElementById('collection-report-want-list');
        if (trigger && panel) {
            trigger.setAttribute('aria-expanded', 'true');
            panel.hidden = false;
        }
    })();
    @elseif (old('report_type') === 'missing' || old('scope'))
    (function () {
        var trigger = document.querySelector('[aria-controls="collection-report-missing-items"]');
        var panel = document.getElementById('collection-report-missing-items');
        if (trigger && panel) {
            trigger.setAttribute('aria-expanded', 'true');
            panel.hidden = false;
        }
    })();
    @elseif (old('set_name') || session('error'))
    (function () {
        var trigger = document.querySelector('[aria-controls="collection-report-set-inventory"]');
        var panel = document.getElementById('collection-report-set-inventory');
        if (trigger && panel) {
            trigger.setAttribute('aria-expanded', 'true');
            panel.hidden = false;
        }
    })();
    @endif
})();
</script>
@endpush

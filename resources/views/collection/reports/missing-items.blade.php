@extends('layouts.app')

@section('title', 'Missing Items Report | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page collection-report-view collection-report-printable">
    <nav class="gallery-breadcrumbs no-print" aria-label="Breadcrumb">
        <ol class="gallery-breadcrumbs-list">
            <li><a href="{{ route('collection.index') }}">My Collection</a></li>
            <li><a href="{{ route('collection.reports.index') }}">Reports</a></li>
            <li aria-current="page">Missing items</li>
        </ol>
    </nav>

    <div class="collection-report-toolbar no-print">
        <button type="button" class="home-primary-btn" onclick="window.print()">Print report</button>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.reports.index') }}">All reports</a>
    </div>

    <header class="collection-report-header">
        <h1 class="collection-report-title">Missing items</h1>
        <p class="collection-report-meta">
            Scope: {{ $scopeLabel }}
            · Sets with at least one owned item: {{ number_format($qualifyingSetCount) }}
            · Collector: {{ auth()->user()->name }} ({{ auth()->user()->username }})
            · {{ $generatedAt->format('M j, Y') }}
        </p>
    </header>

    <p class="collection-report-summary no-print">
        <strong>{{ number_format($rows->count()) }}</strong> missing catalog {{ Str::plural('slot', $rows->count()) }}
        · Click a column heading to sort
    </p>
    <p class="collection-report-summary collection-report-summary--print-only">
        <strong>{{ number_format($rows->count()) }}</strong> missing catalog {{ Str::plural('slot', $rows->count()) }}
    </p>

    @if ($rows->isEmpty())
        <p class="collection-report-empty">No missing items match this report scope.</p>
    @else
        <div class="collection-report-table-wrap">
            <table class="collection-report-table collection-report-table--sortable collection-report-table--checklist" data-sortable-report>
                <thead>
                    <tr>
                        <th scope="col" class="col-check" aria-label="Check off">
                            <span class="collection-report-check-box collection-report-check-box--header" aria-hidden="true"></span>
                        </th>
                        <th scope="col" class="col-num">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="number" data-sort-key="row">#</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="set">Set</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="number" data-sort-key="year">Year</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="code">Set code</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="jurisdiction">Jurisdiction</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="variety">Variety</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="catalog">Plate #</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="want">Want</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $index => $row)
                        @php $plate = $row->plate; @endphp
                        <tr class="collection-report-row collection-report-row--missing"
                            data-sort-row="{{ $index + 1 }}"
                            data-sort-set="{{ strtolower($plate->set_name) }}"
                            data-sort-year="{{ $plate->year ?? 0 }}"
                            data-sort-code="{{ strtolower($plate->set_code) }}"
                            data-sort-jurisdiction="{{ strtolower($plate->jurisdiction ?? '') }}"
                            data-sort-variety="{{ strtolower($plate->variety_notes ?? '') }}"
                            data-sort-catalog="{{ strtolower($plate->serial_number ?? '') }}"
                            data-sort-want="{{ $row->onWantList ? 'yes' : 'no' }}">
                            <td class="col-check">
                                <span class="collection-report-check-box" aria-hidden="true"></span>
                            </td>
                            <td class="col-num collection-report-row-num">{{ $index + 1 }}</td>
                            <td>{{ $plate->set_name }}</td>
                            <td>{{ $plate->year ?? '—' }}</td>
                            <td>{{ $plate->set_code }}</td>
                            <td>
                                <span class="collection-report-jurisdiction">{{ $plate->jurisdiction ? strtoupper($plate->jurisdiction) : '—' }}</span>
                            </td>
                            <td>{{ $plate->variety_notes ?: '—' }}</td>
                            <td>{{ $plate->serial_number ? '#'.$plate->serial_number : '—' }}</td>
                            <td>{{ $row->onWantList ? 'Yes' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p class="collection-report-footer-note">
        Printable checklist — mark each box when you acquire an item. Includes catalog slots with no owned items from sets where you already own at least one item.
    </p>
</div>
@endsection

@push('head')
<style>
@media print {
    .site-header,
    .site-footer,
    .no-print {
        display: none !important;
    }

    .collection-report-summary--print-only {
        display: block !important;
    }

    .collection-report-printable {
        padding: 0;
    }

    .collection-report-table-wrap {
        border: none;
    }

    .collection-report-sort-btn::after {
        display: none !important;
    }

    .collection-report-check-box {
        border-color: #000;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

.collection-report-summary--print-only {
    display: none;
}
</style>
@endpush

@push('scripts')
@include('components.collection-reports-sort-script')
@endpush

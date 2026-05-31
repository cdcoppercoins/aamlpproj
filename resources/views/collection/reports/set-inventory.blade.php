@extends('layouts.app')

@section('title', $setMeta->set_name . ' — Set Inventory Report | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page collection-report-view collection-report-printable">
    <nav class="gallery-breadcrumbs no-print" aria-label="Breadcrumb">
        <ol class="gallery-breadcrumbs-list">
            <li><a href="{{ route('collection.index') }}">My Collection</a></li>
            <li><a href="{{ route('collection.reports.index') }}">Reports</a></li>
            <li aria-current="page">Set inventory</li>
        </ol>
    </nav>

    <div class="collection-report-toolbar no-print">
        <button type="button" class="home-primary-btn" onclick="window.print()">Print report</button>
        <a class="home-primary-btn home-primary-btn-secondary"
           href="{{ route('collection.manage.pdf', ['set_name' => $setMeta->set_name, 'scope' => 'checklist']) }}">
            Download PDF
        </a>
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.reports.index') }}">All reports</a>
        <a class="home-primary-btn home-primary-btn-secondary"
           href="{{ route('collection.manage', ['set_name' => $setMeta->set_name]) }}">Edit this set</a>
    </div>

    <header class="collection-report-header">
        <h1 class="collection-report-title">{{ $setMeta->set_name }}</h1>
        <p class="collection-report-meta">
            @if ($setMeta->company){{ $setMeta->company }} · @endif
            @if ($setMeta->year){{ $setMeta->year }} · @endif
            Set code {{ $setMeta->set_code }}
            · Collector: {{ auth()->user()->name }} ({{ auth()->user()->username }})
            · {{ $generatedAt->format('M j, Y') }}
        </p>
    </header>

    <p class="collection-report-summary">
        <strong>{{ number_format($haveCount) }}</strong> have
        · <strong>{{ number_format($missingCount) }}</strong> missing
        @if ($wantedCount > 0)
            · <strong>{{ number_format($wantedCount) }}</strong> on want list
        @endif
        · {{ number_format($totalInSet) }} catalog slots total
        @if ($setCatalogTotal !== null)
            · Value at your grades (private): {{ \App\Models\Plate::formatCatalogTotal($setCatalogTotal) }}
        @endif
    </p>

    <div class="collection-report-table-wrap">
        <table class="collection-report-table">
            <thead>
                <tr>
                    <th scope="col" class="col-num">#</th>
                    <th scope="col" class="col-jurisdiction">Jurisdiction</th>
                    <th scope="col" class="col-variety">Variety</th>
                    <th scope="col" class="col-status">Status</th>
                    <th scope="col" class="col-serial">Serial</th>
                    <th scope="col" class="col-grade">Grade</th>
                    <th scope="col" class="col-value">Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plates as $index => $plate)
                    @php
                        $entry = $collectionByPlateId->get($plate->id);
                        $jurisdiction = $plate->jurisdiction ? strtoupper($plate->jurisdiction) : '—';
                        $variety = $plate->variety_notes ?: '—';

                        if ($entry && ! $entry->is_wanted && $entry->ownedItemCount() > 0) {
                            $status = 'have';
                            $statusLabel = 'Have';
                        } elseif ($entry?->is_wanted) {
                            $status = 'want';
                            $statusLabel = 'Want';
                        } else {
                            $status = 'missing';
                            $statusLabel = 'Missing';
                        }
                    @endphp

                    @if ($status === 'have')
                        @foreach ($entry->ownedItems as $itemIndex => $ownedItem)
                            @php
                                $isDuplicate = $itemIndex > 0;
                                $entry->setRelation('plate', $plate);
                                $grade = $ownedItem->normalizedGrade();
                                $itemValue = $grade ? $plate->displayCatalogValueForCondition($grade) : '—';
                            @endphp
                            <tr class="collection-report-row collection-report-row--have{{ $isDuplicate ? ' collection-report-row--continued' : '' }}">
                                <td class="col-num">{{ $itemIndex === 0 ? $index + 1 : '' }}</td>
                                <td class="col-jurisdiction">
                                    <span class="collection-report-jurisdiction">{{ $jurisdiction }}</span>
                                    @if (! $isDuplicate && $plate->serial_number)
                                        <span class="collection-report-catalog-id">#{{ $plate->serial_number }}</span>
                                    @endif
                                </td>
                                <td class="col-variety">{{ $variety }}</td>
                                <td class="col-status">
                                    <span class="collection-report-status collection-report-status--have">Have</span>
                                </td>
                                <td class="col-serial">
                                    {{ $ownedItem->serial_number ?: '—' }}
                                </td>
                                <td class="col-grade">
                                    {{ $grade ?: '—' }}
                                </td>
                                <td class="col-value">{{ $itemValue }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="collection-report-row collection-report-row--{{ $status }}">
                            <td class="col-num">{{ $index + 1 }}</td>
                            <td class="col-jurisdiction">
                                <span class="collection-report-jurisdiction">{{ $jurisdiction }}</span>
                                @if ($plate->serial_number)
                                    <span class="collection-report-catalog-id">#{{ $plate->serial_number }}</span>
                                @endif
                            </td>
                            <td class="col-variety">{{ $variety }}</td>
                            <td class="col-status">
                                <span class="collection-report-status collection-report-status--{{ $status }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="col-serial">—</td>
                            <td class="col-grade">—</td>
                            <td class="col-value">—</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="collection-report-footer-note">
        One row per owned item. Have = at least one item recorded for that catalog slot. Missing = no owned items yet.
        Additional copies of the same catalog plate repeat jurisdiction and variety in gray.
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

    .collection-report-printable {
        padding: 0;
    }

    .collection-report-table-wrap {
        border: none;
    }
}
</style>
@endpush

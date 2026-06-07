@extends('layouts.app')

@section('title', 'Member Marketplace | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page collection-marketplace-page">
    <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
        <ol class="gallery-breadcrumbs-list">
            <li><a href="{{ route('collection.index') }}">My Collection</a></li>
            <li aria-current="page">Marketplace</li>
        </ol>
    </nav>

    <header class="collection-marketplace-header">
        <h1 class="home-title">Member marketplace</h1>
        <p class="home-lead">
            Plates and pieces other members have listed for sale or trade. Sort by member or set.
            Items on <strong>your want list</strong> are highlighted.
        </p>
        <p class="collection-marketplace-toolbar">
            <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.marketplace.my-listings') }}">My listings</a>
            <button type="button" class="home-primary-btn home-primary-btn-secondary" data-member-messages-open>Messages</button>
            <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.index') }}">Back to My Collection</a>
        </p>
    </header>

    @if ($listings->isEmpty())
        <p class="collection-empty">No member listings yet. List your own pieces from an <a href="{{ route('collection.index') }}"><strong>Edit entry</strong></a> screen or the per-item <strong>Offer</strong> column when <a href="{{ route('collection.manage') }}"><strong>editing a set</strong></a>.</p>
    @else
        <div class="collection-report-table-wrap">
            <table class="collection-report-table collection-report-table--sortable collection-marketplace-table" data-sortable-report>
                <thead>
                    <tr>
                        <th scope="col" class="col-num">#</th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="member">Member</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="set">Set</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="jurisdiction">Jurisdiction</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="variety">Variety</button>
                        </th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="grade">Grade</button>
                        </th>
                        <th scope="col">Serial</th>
                        <th scope="col">
                            <button type="button" class="collection-report-sort-btn" data-sort-type="text" data-sort-key="listing">Offer</button>
                        </th>
                        <th scope="col">Want list</th>
                        <th scope="col">Contact</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listings as $index => $listing)
                        @php
                            $item = $listing->collectionItem;
                            $plate = $item->plate;
                            $seller = $item->user;
                            $onViewerWantList = $viewerWantedPlateIds->has($plate->id);
                        @endphp
                        <tr class="collection-marketplace-row{{ $onViewerWantList ? ' collection-marketplace-row--want-match' : '' }}"
                            data-sort-member="{{ strtolower($seller->username) }}"
                            data-sort-set="{{ strtolower($plate->set_name) }}"
                            data-sort-jurisdiction="{{ strtolower($plate->jurisdiction ?? '') }}"
                            data-sort-variety="{{ strtolower($plate->variety_notes ?? '') }}"
                            data-sort-grade="{{ strtolower($listing->grade ?? '') }}"
                            data-sort-listing="{{ strtolower($listing->listing_type ?? '') }}">
                            <td class="col-num collection-report-row-num">{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('collection.members.show', $seller->username) }}">{{ $seller->username }}</a>
                            </td>
                            <td>{{ $plate->set_name }}</td>
                            <td>
                                <span class="collection-report-jurisdiction">{{ $plate->jurisdiction ? strtoupper($plate->jurisdiction) : '—' }}</span>
                            </td>
                            <td>{{ $plate->variety_notes ?: '—' }}</td>
                            <td>{{ $listing->grade ?: '—' }}</td>
                            <td>{{ $listing->serial_number ?: '—' }}</td>
                            <td>
                                <span class="collection-marketplace-offer collection-marketplace-offer--{{ $listing->listing_type }}">{{ $listing->listingLabel() }}</span>
                                @if ($listing->listing_notes)
                                    <span class="collection-marketplace-offer-notes">{{ $listing->listing_notes }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($onViewerWantList)
                                    <span class="collection-marketplace-want-badge">On your want list</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <button type="button"
                                        class="gallery-result-btn collection-marketplace-contact-btn"
                                        data-member-messages-open
                                        data-owned-item-id="{{ $listing->id }}"
                                        data-seller-username="{{ $seller->username }}"
                                        data-plate-summary="{{ $plate->set_name }} — {{ strtoupper($plate->jurisdiction ?? 'plate') }}">
                                    Message
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection

@push('scripts')
    @include('components.collection-reports-sort-script')
@endpush

@extends('layouts.app')

@section('title', $setMeta->set_name . ' — ' . $member->name . ' | MiniLicensePlates.com')

@section('robots', 'noindex, follow')

@section('content')
<div class="home-page collection-page">
    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('collection.index') }}">My Collection</a></li>
                <li><a href="{{ route('collection.members.show', $member->username) }}">{{ $member->username }}</a></li>
                <li aria-current="page">{{ $setMeta->set_name }}</li>
            </ol>
        </nav>

        <h1 class="home-title">{{ $setMeta->set_name }}</h1>
        <p class="home-lead">
            {{ $member->name }} (@{{ $member->username }})
            @if ($setMeta->company) · {{ $setMeta->company }} @endif
            @if ($setMeta->year) · {{ $setMeta->year }} @endif
        </p>
    </section>

    <section class="collection-member-set-table-wrap" aria-label="Set contents">
        <table class="collection-manage-table collection-member-readonly-table">
            <thead>
                <tr>
                    <th>Jurisdiction</th>
                    <th>Variety</th>
                    <th>Qty</th>
                    <th>Cond.</th>
                    <th>Want</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $plate)
                    @php $item = $collectionByPlateId[$plate->id]; @endphp
                    <tr class="@if($item->is_wanted) collection-member-want-row @else collection-manage-row-has-entry @endif">
                        <td>
                            {{ $plate->jurisdiction ? strtoupper($plate->jurisdiction) : '—' }}
                            @if ($plate->serial_number)
                                <span class="collection-manage-serial">#{{ $plate->serial_number }}</span>
                            @endif
                        </td>
                        <td>{{ $plate->variety_notes ?: '—' }}</td>
                        <td class="col-qty">{{ $item->is_wanted ? '—' : $item->quantity }}</td>
                        <td class="col-condition">{{ $item->condition ?? '—' }}</td>
                        <td class="col-want">{{ $item->is_wanted ? 'Yes' : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <p class="collection-manage-actions">
        <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('collection.members.show', $member->username) }}">Back to {{ $member->username }}&rsquo;s sets</a>
    </p>
</div>
@endsection

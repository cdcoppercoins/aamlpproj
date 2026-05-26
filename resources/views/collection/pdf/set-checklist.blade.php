<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $setMeta->set_name }} — Collection Checklist</title>
    <style>
        @page {
            margin: 0.45in 0.5in 0.55in 0.5in;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #222;
            line-height: 1.35;
        }

        h1 {
            font-size: 14pt;
            margin: 0 0 4px;
            color: #4079a5;
        }

        .meta {
            margin: 0 0 10px;
            font-size: 8.5pt;
            color: #555;
        }

        .summary {
            margin: 0 0 12px;
            padding: 8px 10px;
            background: #f4f8fb;
            border-left: 3px solid #fab95b;
            font-size: 8.5pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            background: #4079a5;
            color: #fff;
            font-size: 8pt;
            font-weight: bold;
            text-align: left;
            padding: 5px 4px;
            border: 1px solid #356889;
        }

        td {
            padding: 4px;
            border: 1px solid #d8e4ec;
            vertical-align: top;
            font-size: 8.5pt;
        }

        tr.has-entry td {
            background: #f4faf4;
        }

        tr.is-wanted td {
            background: #fff8f0;
        }

        .num {
            width: 22px;
            text-align: center;
            color: #666;
        }

        .jurisdiction {
            font-weight: bold;
            width: 14%;
        }

        .variety {
            width: 18%;
            color: #444;
        }

        .qty, .cond, .want {
            width: 6%;
            text-align: center;
        }

        .location {
            width: 12%;
        }

        .notes {
            width: 22%;
            font-size: 8pt;
            color: #444;
        }

        .serial {
            display: block;
            font-weight: normal;
            font-size: 7.5pt;
            color: #777;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>{{ $setMeta->set_name }}</h1>
    <p class="meta">
        @if ($setMeta->company){{ $setMeta->company }} · @endif
        @if ($setMeta->year){{ $setMeta->year }} · @endif
        Set code {{ $setMeta->set_code }}
        · Collector: {{ $user->name }} ({{ $user->username }})
        · Generated {{ $generatedAt->format('M j, Y') }}
    </p>

    <p class="summary">
        @if ($scope === 'checklist')
            Full set checklist — {{ number_format($totalInSet) }} catalog entries.
            You have recorded {{ number_format($ownedPlateCount) }} owned
            @if ($wantedCount > 0)
                and {{ number_format($wantedCount) }} on your want list
            @endif
            in this set.
        @else
            Your collection entries for this set — {{ number_format($plates->count()) }} rows.
        @endif
        Print on letter-size paper or attach this PDF to email.
    </p>

    <table>
        <thead>
            <tr>
                <th class="num">#</th>
                <th class="jurisdiction">Jurisdiction</th>
                <th class="variety">Variety</th>
                <th class="qty">Qty</th>
                <th class="cond">Cond</th>
                <th class="want">Want</th>
                <th class="location">Location</th>
                <th class="notes">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($plates as $index => $plate)
                @php
                    $entry = $collectionByPlateId[$plate->id] ?? null;
                    $rowClass = $entry
                        ? ($entry->is_wanted ? 'is-wanted' : 'has-entry')
                        : '';
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="num">{{ $index + 1 }}</td>
                    <td class="jurisdiction">
                        {{ $plate->jurisdiction ? strtoupper($plate->jurisdiction) : '—' }}
                        @if ($plate->serial_number)
                            <span class="serial">#{{ $plate->serial_number }}</span>
                        @endif
                    </td>
                    <td class="variety">{{ $plate->variety_notes ?: '—' }}</td>
                    <td class="qty">{{ $entry && ! $entry->is_wanted ? $entry->quantity : ($entry && $entry->is_wanted ? '—' : '') }}</td>
                    <td class="cond">{{ $entry?->condition ?? '' }}</td>
                    <td class="want">{{ $entry?->is_wanted ? 'Yes' : '' }}</td>
                    <td class="location">{{ $entry?->storage_location ?? '' }}</td>
                    <td class="notes">{{ $entry?->notes ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        MiniLicensePlates.com · Personal collection checklist · Not for redistribution
    </div>
</body>
</html>

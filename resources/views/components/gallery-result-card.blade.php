@php
    $frontUrl = $plate->frontImageUrl();
    $backUrl = $plate->backImageUrl();
    $placeholder = asset('plate_missing.png');
    $size = $plate->formattedSize();
    $setCount = $setCounts[$plate->set_code] ?? null;
    $valueFields = [
        'value_mt' => 'Mint',
        'value_ex' => 'EX',
        'value_vg' => 'VG',
        'value_g' => 'GD',
        'value_fr' => 'FR',
        'value_po' => 'PO',
    ];
@endphp
<article class="gallery-result-card">
    <div class="gallery-result-body">
        <div class="gallery-result-image-wrap">
            @if ($backUrl)
                <img class="gallery-result-img thumb-img"
                     src="{{ $frontUrl ?? $placeholder }}"
                     data-hover="{{ $backUrl }}"
                     data-original="{{ $frontUrl ?? $placeholder }}"
                     onerror="this.onerror=null;this.src='{{ $placeholder }}';this.dataset.original='{{ $placeholder }}';this.dataset.hover='';"
                     onmouseover="if(this.dataset.hover){this.src=this.dataset.hover}"
                     onmouseout="this.src=this.dataset.original"
                     alt="{{ $plate->jurisdiction ?? 'Mini license plate' }}">
            @else
                <img class="gallery-result-img thumb-img"
                     src="{{ $frontUrl ?? $placeholder }}"
                     onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                     alt="{{ $plate->jurisdiction ?? 'Mini license plate' }}">
            @endif
        </div>

        <div class="gallery-result-details">
            <div class="gallery-result-copy gallery-result-copy--grid">
                <p class="gallery-result-set">{{ $plate->set_name }}</p>
                <p class="gallery-result-company">{{ $plate->company ?? '' }}</p>
                <p class="gallery-result-jurisdiction">{{ $plate->jurisdiction ? strtoupper($plate->jurisdiction) : '' }}</p>
                <p class="gallery-result-size">{{ $size ? 'size - ' . $size : '' }}</p>
                <p class="gallery-result-variety">{{ $plate->variety_notes ?? '' }}</p>
            </div>

            <div class="gallery-result-panel gallery-result-panel--list">
                <div class="gallery-result-header">
                    <p class="gallery-result-set gallery-result-set--list">
                        {{ $plate->set_name }}@if($plate->jurisdiction) - {{ strtoupper($plate->jurisdiction) }}@endif
                    </p>

                    <p class="gallery-result-subline gallery-result-subline--list">
                        @if ($size)
                            size - {{ $size }}
                        @endif
                        @if ($size && ($setCount || $plate->company))
                            ::
                        @endif
                        @if ($setCount)
                            {{ number_format($setCount) }} plate set
                        @endif
                        @if ($plate->company)
                            by {{ $plate->company }}
                        @endif
                    </p>

                    @if ($plate->variety_notes)
                        <p class="gallery-result-variety gallery-result-variety--list">{{ $plate->variety_notes }}</p>
                    @endif
                </div>

                <div class="gallery-result-bottom">
                    <div class="gallery-result-pricing">
                        <table class="gallery-result-values" aria-label="Catalog values">
                            <thead>
                                <tr>
                                    @foreach ($valueFields as $label)
                                        <th scope="col">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach ($valueFields as $field => $label)
                                        <td>{{ $plate->displayValue($field) }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="gallery-result-actions">
                        <a class="gallery-result-btn" href="#">Learn to Grade</a>
                        <a class="gallery-result-btn" href="{{ route('gallery.show', $plate->set_name) }}">View this Set</a>
                    </div>
                </div>
            </div>

            <div class="gallery-result-main gallery-result-main--grid">
                <div class="gallery-result-pricing">
                    <table class="gallery-result-values" aria-label="Catalog values">
                        <thead>
                            <tr>
                                @foreach ($valueFields as $label)
                                    <th scope="col">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach ($valueFields as $field => $label)
                                    <td>{{ $plate->displayValue($field) }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="gallery-result-actions">
                    <a class="gallery-result-btn" href="#">Learn to Grade</a>
                    <a class="gallery-result-btn" href="{{ route('gallery.show', $plate->set_name) }}">View this Set</a>
                </div>
            </div>
        </div>
    </div>
</article>

@php
    $ownedItem = $ownedItem ?? [];
    $grade = old($oldPrefix.'.'.$index.'.grade', $ownedItem['grade'] ?? null);
    $serial = $ownedItem['serial_number'] ?? null;
    $acquiredDate = old($oldPrefix.'.'.$index.'.acquired_date', $ownedItem['acquired_date'] ?? null);
    $pricePaid = old($oldPrefix.'.'.$index.'.price_paid', $ownedItem['price_paid'] ?? null);
    $storage = old($oldPrefix.'.'.$index.'.storage_location', $ownedItem['storage_location'] ?? null);
    $notes = old($oldPrefix.'.'.$index.'.notes', $ownedItem['notes'] ?? null);
    $listingType = old($oldPrefix.'.'.$index.'.listing_type', $ownedItem['listing_type'] ?? null);
    $listingNotes = old($oldPrefix.'.'.$index.'.listing_notes', $ownedItem['listing_notes'] ?? null);
    $listingTypes = $listingTypes ?? \App\Models\CollectionOwnedItem::LISTING_TYPES;
@endphp

<div class="collection-item-row{{ $compact ? ' collection-item-row--compact' : '' }}" data-item-row>
    @if ($compact)
        <span class="collection-item-row-label"
              title="{{ $serial ? 'Serial '.$serial : 'Item '.($index + 1) }}">#{{ $index + 1 }}</span>
        <select name="{{ $namePrefix }}[{{ $index }}][grade]"
                class="collection-item-grade{{ $inputClass ? ' '.$inputClass : '' }}"
                aria-label="Grade for item {{ $index + 1 }} of {{ $plateLabel }}">
            <option value="">—</option>
            @foreach ($gradeOptions as $code => $label)
                <option value="{{ $code }}" @selected($grade === $code)>{{ $code }}</option>
            @endforeach
        </select>
        <select name="{{ $namePrefix }}[{{ $index }}][listing_type]"
                class="collection-item-listing{{ $inputClass ? ' '.$inputClass : '' }}"
                data-listing-type-select
                aria-label="Listing for item {{ $index + 1 }} of {{ $plateLabel }}">
            @foreach ($listingTypes as $code => $label)
                @php
                    $compactLabel = match ($code) {
                        'sale' => 'Sale',
                        'trade' => 'Trade',
                        'both' => 'Both',
                        default => '—',
                    };
                @endphp
                <option value="{{ $code }}" @selected((string) $listingType === (string) $code)>{{ $compact ? $compactLabel : $label }}</option>
            @endforeach
        </select>
        <button type="button" class="collection-item-remove-btn" data-remove-item aria-label="Remove item {{ $index + 1 }}">×</button>
    @else
        <div class="collection-item-row-head">
            <span class="collection-item-row-label">Item {{ $index + 1 }}</span>
            <button type="button" class="collection-item-remove-btn" data-remove-item aria-label="Remove item {{ $index + 1 }} of {{ $plateLabel }}">Remove</button>
        </div>

        <div class="collection-item-row-fields">
            <label class="collection-item-field">
                <span class="collection-item-field-label">Grade</span>
                <select name="{{ $namePrefix }}[{{ $index }}][grade]"
                        class="collection-item-grade"
                        aria-label="Grade for item {{ $index + 1 }} of {{ $plateLabel }}">
                    <option value="">—</option>
                    @foreach ($gradeOptions as $code => $label)
                        <option value="{{ $code }}" @selected($grade === $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <div class="collection-item-field">
                <span class="collection-item-field-label">Serial #</span>
                <span class="collection-item-serial-display">{{ $serial ?: 'Assigned when saved' }}</span>
            </div>

            <label class="collection-item-field">
                <span class="collection-item-field-label">Date acquired</span>
                <input type="date"
                       name="{{ $namePrefix }}[{{ $index }}][acquired_date]"
                       value="{{ $acquiredDate }}"
                       class="collection-item-acquired">
            </label>

            <label class="collection-item-field">
                <span class="collection-item-field-label">Price paid</span>
                <input type="number"
                       name="{{ $namePrefix }}[{{ $index }}][price_paid]"
                       value="{{ $pricePaid }}"
                       min="0"
                       step="0.01"
                       class="collection-item-price"
                       placeholder="0.00">
            </label>

            <label class="collection-item-field">
                <span class="collection-item-field-label">Storage</span>
                <input type="text"
                       name="{{ $namePrefix }}[{{ $index }}][storage_location]"
                       value="{{ $storage }}"
                       maxlength="128"
                       class="collection-item-storage"
                       placeholder="Binder, box…">
            </label>

            <label class="collection-item-field collection-item-field--wide">
                <span class="collection-item-field-label">Notes</span>
                <textarea name="{{ $namePrefix }}[{{ $index }}][notes]"
                          rows="2"
                          maxlength="5000"
                          class="collection-item-notes">{{ $notes }}</textarea>
            </label>

            <label class="collection-item-field">
                <span class="collection-item-field-label">Offer</span>
                <select name="{{ $namePrefix }}[{{ $index }}][listing_type]"
                        class="collection-item-listing"
                        data-listing-type-select
                        aria-label="Listing for item {{ $index + 1 }} of {{ $plateLabel }}">
                    @foreach ($listingTypes as $code => $label)
                        <option value="{{ $code }}" @selected((string) $listingType === (string) $code)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="collection-item-field collection-item-field--wide">
                <span class="collection-item-field-label">Listing notes</span>
                <input type="text"
                       name="{{ $namePrefix }}[{{ $index }}][listing_notes]"
                       value="{{ $listingNotes }}"
                       maxlength="500"
                       class="collection-item-listing-notes"
                       placeholder="Price, trade preferences, shipping…">
            </label>
        </div>
    @endif
</div>


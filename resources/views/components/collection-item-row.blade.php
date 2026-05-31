@php
    $ownedItem = $ownedItem ?? [];
    $grade = old($oldPrefix.'.'.$index.'.grade', $ownedItem['grade'] ?? null);
    $serial = $ownedItem['serial_number'] ?? null;
    $acquiredDate = old($oldPrefix.'.'.$index.'.acquired_date', $ownedItem['acquired_date'] ?? null);
    $pricePaid = old($oldPrefix.'.'.$index.'.price_paid', $ownedItem['price_paid'] ?? null);
    $storage = old($oldPrefix.'.'.$index.'.storage_location', $ownedItem['storage_location'] ?? null);
    $notes = old($oldPrefix.'.'.$index.'.notes', $ownedItem['notes'] ?? null);
@endphp

<div class="collection-item-row{{ $compact ? ' collection-item-row--compact' : '' }}" data-item-row>
    @if ($compact)
        <span class="collection-item-row-label">#{{ $index + 1 }}</span>
        <select name="{{ $namePrefix }}[{{ $index }}][grade]"
                class="collection-item-grade{{ $inputClass ? ' '.$inputClass : '' }}"
                aria-label="Grade for item {{ $index + 1 }} of {{ $plateLabel }}">
            <option value="">—</option>
            @foreach ($gradeOptions as $code => $label)
                <option value="{{ $code }}" @selected($grade === $code)>{{ $code }}</option>
            @endforeach
        </select>
        <span class="collection-item-serial-display" aria-label="Serial number for item {{ $index + 1 }} of {{ $plateLabel }}">
            {{ $serial ?: '—' }}
        </span>
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
        </div>
    @endif
</div>

@php
    $namePrefix = $namePrefix ?? 'owned_items';
    $itemRows = $itemRows ?? [];
    $gradeOptions = $gradeOptions ?? \App\Models\CollectionItem::GRADES;
    $compact = $compact ?? false;
    $plateLabel = $plateLabel ?? 'plate';
    $oldPrefix = str_replace(['[', ']'], ['.', ''], $namePrefix);
@endphp

<div class="collection-items-editor{{ $compact ? ' collection-items-editor--compact' : '' }}"
     data-collection-items
     data-name-prefix="{{ $namePrefix }}"
     data-plate-label="{{ $plateLabel }}"
     data-compact="{{ $compact ? '1' : '0' }}">
    <div class="collection-items-list" data-items-list>
        @foreach ($itemRows as $index => $ownedItem)
            @include('components.collection-item-row', [
                'namePrefix' => $namePrefix,
                'index' => $index,
                'ownedItem' => $ownedItem,
                'gradeOptions' => $gradeOptions,
                'compact' => $compact,
                'plateLabel' => $plateLabel,
                'oldPrefix' => $oldPrefix,
                'inputClass' => $inputClass ?? '',
            ])
        @endforeach
    </div>
    <button type="button" class="collection-items-add-btn" data-add-item>Add item</button>
</div>

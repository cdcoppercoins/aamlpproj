@php
    $namePrefix = $namePrefix ?? 'copy_conditions';
    $quantity = max(0, (int) ($quantity ?? 1));
    $selectedGrades = $selectedGrades ?? [];
    $gradeOptions = $gradeOptions ?? \App\Models\CollectionItem::GRADES;
    $compact = $compact ?? false;
    $plateLabel = $plateLabel ?? 'plate';
@endphp

<div class="collection-copy-grades{{ $compact ? ' collection-copy-grades--compact' : '' }}"
     data-copy-grades
     data-name-prefix="{{ $namePrefix }}"
     data-plate-label="{{ $plateLabel }}"
     @if ($quantity <= 0) hidden @endif>
    @for ($index = 0; $index < $quantity; $index++)
        @php $selected = old(str_replace(['[', ']'], ['.', ''], $namePrefix).'.'.$index, $selectedGrades[$index] ?? null); @endphp
        <label class="collection-copy-grade">
            @if (! $compact)
                <span class="collection-copy-grade-label">Copy {{ $index + 1 }}</span>
            @else
                <span class="collection-copy-grade-label">#{{ $index + 1 }}</span>
            @endif
            <select name="{{ $namePrefix }}[{{ $index }}]"
                    class="collection-copy-grade-select{{ $inputClass ? ' '.$inputClass : '' }}"
                    aria-label="Grade for copy {{ $index + 1 }} of {{ $plateLabel }}">
                <option value="">—</option>
                @foreach ($gradeOptions as $code => $label)
                    <option value="{{ $code }}" @selected($selected === $code)>{{ $compact ? $code : $label }}</option>
                @endforeach
            </select>
        </label>
    @endfor
</div>

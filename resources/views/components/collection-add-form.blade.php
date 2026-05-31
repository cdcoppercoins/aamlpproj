@php
    use App\Models\CollectionItem;
@endphp
<div class="collection-add-wrap">
    @auth
        @if ($collectionEntry)
            <p class="collection-add-status">
                In your collection<br>
                @if ($collectionEntry->is_wanted)
                    (want list)
                @else
                    {{ $collectionEntry->ownedItemCount() }} {{ $collectionEntry->ownedItemCount() === 1 ? 'item' : 'items' }}@if ($collectionEntry->gradeSummary()) · {{ $collectionEntry->gradeSummary() }}@endif
                @endif
                <br>
                <a href="{{ route('collection.edit', $collectionEntry) }}">Edit/Add</a>
            </p>
        @else
            <form class="collection-add-form" method="post" action="{{ route('collection.store') }}">
                @csrf
                <input type="hidden" name="plate_id" value="{{ $plate->id }}">
                <div class="collection-add-fields">
                    <label class="collection-add-field">
                        <span class="collection-add-label">Grade</span>
                        <select name="grade" class="collection-add-grade">
                            <option value="">—</option>
                            @foreach (CollectionItem::GRADES as $code => $label)
                                <option value="{{ $code }}">{{ $code }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="collection-add-buttons">
                    <button type="submit" class="collection-add-btn">Add item</button>
                    <button type="submit" name="is_wanted" value="1" class="collection-add-btn collection-add-btn-want">Want list</button>
                </div>
            </form>
        @endif
    @else
        <p class="collection-add-guest">
            <a href="{{ route('login') }}">Sign in</a> to track this plate in your collection.
        </p>
    @endauth
</div>

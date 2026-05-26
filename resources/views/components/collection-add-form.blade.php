@php
    use App\Models\CollectionItem;
@endphp
<div class="collection-add-wrap">
    @auth
        @if ($collectionEntry)
            <p class="collection-add-status">
                In your collection
                @if ($collectionEntry->is_wanted)
                    (want list)
                @else
                    · qty {{ $collectionEntry->quantity }}
                    @if ($collectionEntry->condition)
                        · {{ $collectionEntry->condition }}
                    @endif
                @endif
                — <a href="{{ route('collection.edit', $collectionEntry) }}">Edit</a>
            </p>
        @else
            <form class="collection-add-form" method="post" action="{{ route('collection.store') }}">
                @csrf
                <input type="hidden" name="plate_id" value="{{ $plate->id }}">
                <div class="collection-add-fields">
                    <label class="collection-add-field">
                        <span class="collection-add-label">Qty</span>
                        <input type="number" name="quantity" value="1" min="1" max="9999" class="collection-add-qty">
                    </label>
                    <label class="collection-add-field">
                        <span class="collection-add-label">Cond.</span>
                        <select name="condition" class="collection-add-condition">
                            <option value="">—</option>
                            @foreach (CollectionItem::CONDITIONS as $code => $label)
                                <option value="{{ $code }}">{{ $code }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="collection-add-buttons">
                    <button type="submit" class="collection-add-btn">Add to collection</button>
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

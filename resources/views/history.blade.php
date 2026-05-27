@extends('layouts.app')

@section('title', 'History of Mini License Plate Premiums | MiniLicensePlates.com')

@section('meta_description', 'The history of miniature license plate premiums packaged with candy, gum, cereal, and other products. Explore a timeline of how manufacturers used plate toys to reach collectors.')

@section('canonical_url', route('history'))

@section('content')
<div class="home-page history-page">
    <h1>History of miniature license plate premiums</h1>
    <p class="history-intro">
        Miniature license plates were packaged with candy, gum, cereal, tobacco, and countless other products for decades.
        Use the timeline below to explore key periods — each marker opens a short story and photograph.
    </p>
    <p class="history-hint">
        Click a label on the timeline to open its story and photo.
        Close with the <strong>×</strong> button in the corner or press <kbd>Esc</kbd>.
    </p>

    @if (count($timelineEntries) === 0)
        <p class="history-empty">Timeline content is being prepared.</p>
    @else
        <div class="history-interactive" id="historyInteractive">
        <div class="history-timeline-wrap" id="history-timeline">
            <div class="history-timeline-track" role="list" aria-label="History timeline">
                @foreach ($timelineEntries as $index => $entry)
                    <div class="history-timeline-item" role="listitem">
                        <button type="button"
                                class="history-timeline-marker"
                                id="history-marker-{{ $entry['id'] }}"
                                data-history-id="{{ $entry['id'] }}"
                                data-history-title="{{ e($entry['title']) }}"
                                data-history-image="{{ $entry['image_url'] ?? '' }}"
                                data-history-alt="{{ e($entry['alt']) }}"
                                aria-expanded="false"
                                aria-controls="historyModal"
                                @if ($index === 0) aria-current="false" @endif>
                            <span class="history-timeline-dot" aria-hidden="true"></span>
                            <span class="history-timeline-label">{{ $entry['label'] }}</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <p class="history-preview-placeholder" id="historyPreviewPlaceholder">
            Click a label in the timeline list to read that story and see its photo.
        </p>

        <div id="historyModal"
             class="history-modal"
             role="dialog"
             aria-modal="true"
             aria-labelledby="historyModalTitle"
             aria-describedby="historyModalBody"
             hidden>
            <div class="history-modal-backdrop" aria-hidden="true"></div>
            <div class="history-modal-panel">
                <div class="history-modal-header">
                    <h2 class="history-modal-title" id="historyModalTitle"></h2>
                    <button type="button" class="history-modal-close" data-history-close aria-label="Close">&times;</button>
                </div>
                <div class="history-modal-scroll">
                    <div class="history-modal-layout" id="historyModalLayout">
                        <aside class="history-modal-media" id="historyModalMedia" hidden aria-label="Illustration">
                            <img id="historyModalImg" src="" alt="">
                            <p class="image-caption" id="historyModalCaption" hidden></p>
                        </aside>
                        <div class="history-modal-body" id="historyModalBody"></div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        @foreach ($timelineEntries as $entry)
            <div id="history-content-{{ $entry['id'] }}" class="history-entry-source" hidden>
                {!! $entry['body'] !!}
            </div>
            @if (! empty($entry['caption']))
                <div id="history-caption-{{ $entry['id'] }}" class="history-caption-source" hidden>{{ $entry['caption'] }}</div>
            @endif
        @endforeach
    @endif
</div>
@endsection

@push('scripts')
@include('components.history-timeline-script')
@endpush

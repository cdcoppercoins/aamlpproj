@extends('layouts.app')

@section('title', 'History of Mini License Plate Premiums | MiniLicensePlates.com')

@section('meta_description', 'The history of miniature license plate premiums packaged with candy, gum, cereal, and other products. Explore a timeline of how manufacturers used plate toys to reach collectors.')

@section('canonical_url', route('history'))

@section('content')
<div class="home-page history-page">
    <h1>History of miniature license plate premiums</h1>
    <p class="history-intro">
        Miniature license plates were packaged with candy, gum, cereal, tobacco, and countless other products for decades.
        Use the timeline below to explore key periods — tap or click a row to expand its story and photograph.
    </p>
    <p class="history-hint">
        Expand a period below to read the full story. Only one section stays open at a time.
    </p>

    @if (count($timelineEntries) === 0)
        <p class="history-empty">Timeline content is being prepared.</p>
    @else
        <div class="history-interactive" id="historyInteractive">
            <div class="history-timeline-wrap" id="history-timeline">
                <div class="history-timeline-track" aria-label="History timeline">
                    @foreach ($timelineEntries as $entry)
                        <details class="history-accordion-item" name="history-timeline">
                            <summary class="history-timeline-marker" id="history-marker-{{ $entry['id'] }}">
                                <span class="history-timeline-thumb{{ empty($entry['image_url']) ? ' history-timeline-thumb--empty' : '' }}" aria-hidden="true">
                                    @if (! empty($entry['image_url']))
                                        <img src="{{ $entry['image_url'] }}" alt="" width="60" height="60" loading="lazy" decoding="async">
                                    @endif
                                </span>
                                <span class="history-timeline-label">{{ $entry['label'] }}</span>
                            </summary>
                            <div class="history-accordion-panel" id="history-panel-{{ $entry['id'] }}">
                                <h2 class="history-accordion-title">{{ $entry['title'] }}</h2>
                                <div class="history-accordion-layout{{ empty($entry['image_url']) && empty($entry['caption']) ? '' : ' has-media' }}">
                                    @if (! empty($entry['image_url']) || ! empty($entry['caption']))
                                        <figure class="history-accordion-media" aria-label="Illustration">
                                            @if (! empty($entry['image_url']))
                                                <img src="{{ $entry['image_url'] }}" alt="{{ $entry['alt'] }}">
                                            @endif
                                            @if (! empty($entry['caption']))
                                                <figcaption class="image-caption">{{ $entry['caption'] }}</figcaption>
                                            @endif
                                        </figure>
                                    @endif
                                    <div class="history-accordion-body" lang="en">
                                        {!! $entry['body'] !!}
                                    </div>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

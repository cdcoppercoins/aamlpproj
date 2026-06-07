@extends('layouts.app')

@section('title', 'Newsletter — ' . $campaign->subject . ' | Admin')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.newsletter.campaigns.index') }}">Member emails</a></li>
                <li aria-current="page">{{ $campaign->subject }}</li>
            </ol>
        </nav>
        <h1 class="home-title">{{ $campaign->subject }}</h1>
        <p class="home-lead">
            Status: <strong>{{ $campaign->statusLabel() }}</strong>
            · Created {{ $campaign->created_at?->format('M j, Y g:i A') }}
            @if ($campaign->creator)
                by {{ $campaign->creator->username }}
            @endif
        </p>
    </section>

    <section class="admin-panel admin-newsletter-show">
        <dl class="admin-detail-list">
            <div>
                <dt>Recipients queued</dt>
                <dd>{{ number_format($campaign->recipient_count) }}</dd>
            </div>
            <div>
                <dt>Sent</dt>
                <dd>{{ number_format($campaign->sent_count) }}</dd>
            </div>
            <div>
                <dt>Failed</dt>
                <dd>{{ number_format($campaign->failed_count) }}</dd>
            </div>
            <div>
                <dt>Skipped</dt>
                <dd>{{ number_format($campaign->skipped_count) }}</dd>
            </div>
            @if ($campaign->test_sent_at)
                <div>
                    <dt>Test sent</dt>
                    <dd>{{ $campaign->test_sent_at->format('M j, Y g:i A') }}</dd>
                </div>
            @endif
            @if ($campaign->send_started_at)
                <div>
                    <dt>Send started</dt>
                    <dd>{{ $campaign->send_started_at->format('M j, Y g:i A') }}</dd>
                </div>
            @endif
            @if ($campaign->send_completed_at)
                <div>
                    <dt>Send completed</dt>
                    <dd>{{ $campaign->send_completed_at->format('M j, Y g:i A') }}</dd>
                </div>
            @endif
        </dl>

        <p class="admin-panel-actions">
            <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('admin.newsletter.campaigns.preview', $campaign) }}">Preview</a>
            @if ($campaign->isSending())
                <a class="home-primary-btn" href="{{ route('admin.newsletter.campaigns.sending', $campaign) }}">Continue sending</a>
            @elseif ($campaign->isEditable())
                <a class="home-primary-btn" href="{{ route('admin.newsletter.campaigns.edit', $campaign) }}">Edit draft</a>
                <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('admin.newsletter.campaigns.confirm-send', $campaign) }}">Send to members…</a>
            @endif
            <a class="admin-inline-link" href="{{ route('admin.newsletter.campaigns.index') }}">Back to list</a>
        </p>

        <div class="admin-newsletter-preview-box">
            <h2 class="admin-panel-title">Body</h2>
            <div class="admin-newsletter-preview-body">{!! $campaign->body_html !!}</div>
        </div>
    </section>
</div>
@endsection

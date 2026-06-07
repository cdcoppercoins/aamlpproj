@extends('layouts.app')

@section('title', 'Sending — ' . $campaign->subject . ' | Admin')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <h1 class="home-title">Sending newsletter</h1>
        <p class="home-lead" id="newsletter-sending-status">
            @if ($campaign->status === \App\Models\MemberNewsletterCampaign::STATUS_SENT)
                Send complete.
            @else
                Preparing to send…
            @endif
        </p>
    </section>

    <section class="admin-panel">
        <div class="admin-newsletter-progress" aria-live="polite">
            <p><strong>{{ $campaign->subject }}</strong></p>
            <p>
                Sent: <span id="newsletter-sent-count">{{ number_format($campaign->sent_count) }}</span>
                · Failed: <span id="newsletter-failed-count">{{ number_format($campaign->failed_count) }}</span>
                · Pending: <span id="newsletter-pending-count">{{ number_format($campaign->pendingDeliveryCount()) }}</span>
                · Total: <span id="newsletter-total-count">{{ number_format($campaign->recipient_count) }}</span>
            </p>
            <div class="admin-newsletter-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="admin-newsletter-progress-fill" id="newsletter-progress-fill" style="width:0%"></div>
            </div>
        </div>

        @if ($campaign->isSending())
            <form method="post" action="{{ $cancelUrl }}" class="admin-inline-form" onsubmit="return confirm('Cancel remaining unsent emails?');">
                @csrf
                <button type="submit" class="admin-inline-danger-btn">Cancel remaining</button>
            </form>
        @endif

        <p class="auth-actions">
            <a class="home-primary-btn home-primary-btn-secondary" id="newsletter-done-link" href="{{ $showUrl }}" @if($campaign->isSending()) hidden @endif>View campaign summary</a>
            <a class="admin-inline-link" href="{{ route('admin.newsletter.campaigns.index') }}">Back to list</a>
        </p>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var batchUrl = @json($batchUrl);
    var done = @json(! $campaign->isSending());
    var csrf = @json(csrf_token());
    var statusEl = document.getElementById('newsletter-sending-status');
    var sentEl = document.getElementById('newsletter-sent-count');
    var failedEl = document.getElementById('newsletter-failed-count');
    var pendingEl = document.getElementById('newsletter-pending-count');
    var totalEl = document.getElementById('newsletter-total-count');
    var fillEl = document.getElementById('newsletter-progress-fill');
    var barEl = document.querySelector('.admin-newsletter-progress-bar');
    var doneLink = document.getElementById('newsletter-done-link');

    function updateProgress(totals) {
        if (!totals) return;
        sentEl.textContent = Number(totals.sent_count || 0).toLocaleString();
        failedEl.textContent = Number(totals.failed_count || 0).toLocaleString();
        var total = Number(totals.recipient_count || 0);
        var sent = Number(totals.sent_count || 0) + Number(totals.failed_count || 0) + Number(totals.skipped_count || 0);
        var pending = Math.max(0, total - sent);
        pendingEl.textContent = pending.toLocaleString();
        totalEl.textContent = total.toLocaleString();
        var pct = total > 0 ? Math.round((sent / total) * 100) : 0;
        fillEl.style.width = pct + '%';
        if (barEl) barEl.setAttribute('aria-valuenow', String(pct));
    }

    function finish(message) {
        done = true;
        statusEl.textContent = message;
        if (doneLink) doneLink.hidden = false;
    }

    function sendBatch() {
        if (done) return;

        fetch(batchUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) throw new Error(data.error || 'Send batch failed.');
                    return data;
                });
            })
            .then(function (data) {
                updateProgress(data.totals);
                if (data.done) {
                    finish('Send complete.');
                    return;
                }
                statusEl.textContent = 'Sending… (' + data.pending + ' remaining)';
                window.setTimeout(sendBatch, 400);
            })
            .catch(function (error) {
                statusEl.textContent = 'Error: ' + error.message + ' — refresh this page to continue.';
            });
    }

    updateProgress({
        recipient_count: {{ (int) $campaign->recipient_count }},
        sent_count: {{ (int) $campaign->sent_count }},
        failed_count: {{ (int) $campaign->failed_count }},
        skipped_count: {{ (int) $campaign->skipped_count }},
    });

    if (!done) {
        sendBatch();
    } else {
        finish('Send complete.');
    }
})();
</script>
@endpush

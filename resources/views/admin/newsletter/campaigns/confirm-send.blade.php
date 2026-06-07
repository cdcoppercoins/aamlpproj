@extends('layouts.app')

@section('title', 'Confirm Send — ' . $campaign->subject . ' | Admin')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <h1 class="home-title">Confirm send to members</h1>
        <p class="home-lead">Review carefully. This cannot be undone after delivery begins.</p>
    </section>

    <section class="admin-panel admin-panel-danger">
        <h2 class="admin-panel-title">{{ $campaign->subject }}</h2>
        <ul class="admin-newsletter-confirm-list">
            <li><strong>{{ number_format($eligibleCount) }}</strong> registered members will be queued (not blocked, not unsubscribed).</li>
            <li>Emails send in small batches from the next screen — keep that tab open until finished.</li>
            <li>Each email includes an unsubscribe link.</li>
            <li>Send yourself a <strong>test email</strong> first if you have not already.</li>
        </ul>

        <form class="admin-form" method="post" action="{{ route('admin.newsletter.campaigns.send', $campaign) }}">
            @csrf
            <label class="auth-field">
                <span class="auth-label">Type <strong>SEND TO MEMBERS</strong> to confirm</span>
                <input type="text" name="confirm_phrase" value="{{ old('confirm_phrase') }}" autocomplete="off" required>
                @error('confirm_phrase')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </label>
            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Queue and start sending</button>
                <a class="admin-inline-link" href="{{ route('admin.newsletter.campaigns.edit', $campaign) }}">Go back</a>
            </p>
        </form>
    </section>
</div>
@endsection

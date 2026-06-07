@extends('layouts.app')

@section('title', 'Preview — ' . $campaign->subject . ' | Admin')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <h1 class="home-title">Email preview</h1>
        <p class="home-lead">This is how the message will look in a member&rsquo;s inbox.</p>
    </section>

    <section class="admin-panel">
        <div class="admin-newsletter-email-preview">
            @include('emails.member-newsletter', [
                'campaign' => $campaign,
                'recipient' => $previewUser,
                'unsubscribeUrl' => $unsubscribeUrl,
                'isTest' => true,
            ])
        </div>
        <p class="auth-actions">
            <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('admin.newsletter.campaigns.edit', $campaign) }}">Back to editor</a>
        </p>
    </section>
</div>
@endsection

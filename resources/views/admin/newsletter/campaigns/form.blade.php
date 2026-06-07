@extends('layouts.app')

@section('title', ($campaign->exists ? 'Edit' : 'New') . ' Member Newsletter | Admin')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.newsletter.campaigns.index') }}">Member emails</a></li>
                <li aria-current="page">{{ $campaign->exists ? 'Edit draft' : 'New newsletter' }}</li>
            </ol>
        </nav>
        <h1 class="home-title">{{ $campaign->exists ? 'Edit newsletter draft' : 'New member newsletter' }}</h1>
        <p class="home-lead">
            {{ number_format($eligibleMemberCount) }} members can receive email (not blocked, not unsubscribed).
            Save a draft, preview, send yourself a test, then confirm before sending to everyone.
        </p>
    </section>

    <section class="admin-panel">
        @if ($errors->any())
            <ul class="admin-flash-errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form class="admin-form" method="post" action="{{ $campaign->exists ? route('admin.newsletter.campaigns.update', $campaign) : route('admin.newsletter.campaigns.store') }}">
            @csrf
            @if ($campaign->exists)
                @method('PUT')
            @endif

            @include('components.admin-newsletter-campaign-form', ['campaign' => $campaign])

            <p class="auth-actions">
                <button type="submit" class="home-primary-btn">Save draft</button>
                @if ($campaign->exists)
                    <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('admin.newsletter.campaigns.preview', $campaign) }}">Preview</a>
                    <button type="submit"
                            class="home-primary-btn home-primary-btn-secondary"
                            formaction="{{ route('admin.newsletter.campaigns.test-send', $campaign) }}"
                            formmethod="post">
                        Send test to me
                    </button>
                    <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('admin.newsletter.campaigns.confirm-send', $campaign) }}">Send to members…</a>
                @endif
                <a class="admin-inline-link" href="{{ route('admin.newsletter.campaigns.index') }}">Cancel</a>
            </p>
        </form>
    </section>
</div>
@endsection

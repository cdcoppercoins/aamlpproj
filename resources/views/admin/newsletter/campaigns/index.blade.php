@extends('layouts.app')

@section('title', 'Admin — Member Newsletters | MiniLicensePlates.com')

@section('robots', 'noindex, nofollow')

@section('content')
<div class="home-page admin-page">
    @include('components.admin-nav')

    <section class="home-hero gallery-hero">
        <nav class="gallery-breadcrumbs" aria-label="Breadcrumb">
            <ol class="gallery-breadcrumbs-list">
                <li><a href="{{ route('admin.newsletter.index') }}">Newsletter</a></li>
                <li aria-current="page">Member emails</li>
            </ol>
        </nav>
        <h1 class="home-title">Member email newsletters</h1>
        <p class="home-lead">
            Compose and send email to registered members who are not blocked and have not unsubscribed.
            Currently <strong>{{ number_format($eligibleMemberCount) }}</strong> eligible members.
        </p>
        <p class="admin-panel-actions">
            <a class="home-primary-btn" href="{{ route('admin.newsletter.campaigns.create') }}">New newsletter</a>
            <a class="home-primary-btn home-primary-btn-secondary" href="{{ route('admin.newsletter.index') }}">Footer subscribers</a>
        </p>
    </section>

    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Subject</th>
                        <th scope="col">Status</th>
                        <th scope="col">Recipients</th>
                        <th scope="col">Created</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        <tr>
                            <td>
                                <a href="{{ route('admin.newsletter.campaigns.show', $campaign) }}">{{ $campaign->subject }}</a>
                            </td>
                            <td>{{ $campaign->statusLabel() }}</td>
                            <td>
                                @if ($campaign->recipient_count > 0)
                                    {{ number_format($campaign->sent_count) }} sent
                                    @if ($campaign->failed_count > 0)
                                        · {{ number_format($campaign->failed_count) }} failed
                                    @endif
                                    / {{ number_format($campaign->recipient_count) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $campaign->created_at?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                @if ($campaign->isSending())
                                    <a class="admin-inline-link" href="{{ route('admin.newsletter.campaigns.sending', $campaign) }}">Continue sending</a>
                                @elseif ($campaign->isEditable())
                                    <a class="admin-inline-link" href="{{ route('admin.newsletter.campaigns.edit', $campaign) }}">Edit</a>
                                @else
                                    <a class="admin-inline-link" href="{{ route('admin.newsletter.campaigns.show', $campaign) }}">View</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty-cell">No newsletters yet. Create a draft to get started.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($campaigns->hasPages())
            <div class="admin-pagination">
                @if ($campaigns->onFirstPage())
                    <span class="admin-pagination-disabled">Previous</span>
                @else
                    <a href="{{ $campaigns->previousPageUrl() }}" class="admin-inline-link">Previous</a>
                @endif
                <span class="admin-pagination-info">Page {{ $campaigns->currentPage() }} of {{ $campaigns->lastPage() }}</span>
                @if ($campaigns->hasMorePages())
                    <a href="{{ $campaigns->nextPageUrl() }}" class="admin-inline-link">Next</a>
                @else
                    <span class="admin-pagination-disabled">Next</span>
                @endif
            </div>
        @endif
    </section>
</div>
@endsection

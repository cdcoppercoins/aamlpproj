<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberNewsletterCampaign;
use App\Services\MemberNewsletterSender;
use App\Support\MemberNewsletterRecipients;
use App\Support\PageHtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class NewsletterCampaignController extends Controller
{
    public function __construct(
        private readonly MemberNewsletterSender $sender,
    ) {}

    public function index(): View
    {
        $campaigns = MemberNewsletterCampaign::query()
            ->with('creator')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.newsletter.campaigns.index', [
            'campaigns' => $campaigns,
            'eligibleMemberCount' => MemberNewsletterRecipients::eligibleCount(),
        ]);
    }

    public function create(): View
    {
        return view('admin.newsletter.campaigns.form', [
            'campaign' => new MemberNewsletterCampaign([
                'status' => MemberNewsletterCampaign::STATUS_DRAFT,
            ]),
            'eligibleMemberCount' => MemberNewsletterRecipients::eligibleCount(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCampaign($request);
        $validated['body_html'] = PageHtmlSanitizer::clean($validated['body_html']);
        $validated['created_by'] = Auth::id();
        $validated['status'] = MemberNewsletterCampaign::STATUS_DRAFT;

        $campaign = MemberNewsletterCampaign::query()->create($validated);

        return redirect()
            ->route('admin.newsletter.campaigns.edit', $campaign)
            ->with('success', 'Newsletter draft saved.');
    }

    public function edit(MemberNewsletterCampaign $campaign): View|RedirectResponse
    {
        if (! $campaign->isEditable()) {
            return redirect()
                ->route('admin.newsletter.campaigns.show', $campaign)
                ->with('error', 'Sent campaigns cannot be edited.');
        }

        return view('admin.newsletter.campaigns.form', [
            'campaign' => $campaign,
            'eligibleMemberCount' => MemberNewsletterRecipients::eligibleCount(),
        ]);
    }

    public function update(Request $request, MemberNewsletterCampaign $campaign): RedirectResponse
    {
        if (! $campaign->isEditable()) {
            return back()->with('error', 'Sent campaigns cannot be edited.');
        }

        $validated = $this->validateCampaign($request);
        $validated['body_html'] = PageHtmlSanitizer::clean($validated['body_html']);

        $campaign->update($validated);

        return redirect()
            ->route('admin.newsletter.campaigns.edit', $campaign)
            ->with('success', 'Newsletter draft updated.');
    }

    public function show(MemberNewsletterCampaign $campaign): View
    {
        $campaign->load(['creator', 'sender']);

        return view('admin.newsletter.campaigns.show', [
            'campaign' => $campaign,
            'eligibleMemberCount' => MemberNewsletterRecipients::eligibleCount(),
        ]);
    }

    public function preview(MemberNewsletterCampaign $campaign): View
    {
        return view('admin.newsletter.campaigns.preview', [
            'campaign' => $campaign,
            'previewUser' => Auth::user(),
            'unsubscribeUrl' => $this->sender->unsubscribeUrlFor(Auth::user()),
        ]);
    }

    public function destroy(MemberNewsletterCampaign $campaign): RedirectResponse
    {
        if (! $campaign->isEditable()) {
            return back()->with('error', 'Only draft campaigns can be deleted.');
        }

        $campaign->delete();

        return redirect()
            ->route('admin.newsletter.campaigns.index')
            ->with('success', 'Draft deleted.');
    }

    public function testSend(MemberNewsletterCampaign $campaign): RedirectResponse
    {
        if (! $campaign->isEditable()) {
            return back()->with('error', 'Only draft campaigns can be test-sent.');
        }

        try {
            $this->sender->sendTest($campaign, Auth::user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Test email sent to ' . Auth::user()->email . '.');
    }

    public function confirmSend(MemberNewsletterCampaign $campaign): View|RedirectResponse
    {
        if (! $campaign->isEditable()) {
            return redirect()
                ->route('admin.newsletter.campaigns.show', $campaign)
                ->with('error', 'This campaign has already been sent or is sending.');
        }

        $eligibleCount = MemberNewsletterRecipients::eligibleCount();

        return view('admin.newsletter.campaigns.confirm-send', [
            'campaign' => $campaign,
            'eligibleCount' => $eligibleCount,
        ]);
    }

    public function initiateSend(Request $request, MemberNewsletterCampaign $campaign): RedirectResponse
    {
        if (! $campaign->isEditable()) {
            return back()->with('error', 'This campaign has already been sent or is sending.');
        }

        $request->validate([
            'confirm_phrase' => ['required', 'string', 'in:SEND TO MEMBERS'],
        ]);

        try {
            $count = $this->sender->queueCampaign($campaign, Auth::user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.newsletter.campaigns.sending', $campaign)
            ->with('success', "Queued {$count} member emails. Sending will continue in batches.");
    }

    public function sending(MemberNewsletterCampaign $campaign): View|RedirectResponse
    {
        if (! $campaign->isSending() && $campaign->status !== MemberNewsletterCampaign::STATUS_SENT) {
            return redirect()
                ->route('admin.newsletter.campaigns.show', $campaign);
        }

        $campaign->refresh();

        return view('admin.newsletter.campaigns.sending', [
            'campaign' => $campaign,
            'batchUrl' => route('admin.newsletter.campaigns.send-batch', $campaign),
            'cancelUrl' => route('admin.newsletter.campaigns.cancel', $campaign),
            'showUrl' => route('admin.newsletter.campaigns.show', $campaign),
        ]);
    }

    public function sendBatch(MemberNewsletterCampaign $campaign): JsonResponse
    {
        try {
            $result = $this->sender->sendNextBatch($campaign);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $campaign->refresh();

        return response()->json([
            'sent' => $result['sent'],
            'failed' => $result['failed'],
            'pending' => $result['pending'],
            'done' => $result['done'],
            'status' => $campaign->status,
            'totals' => [
                'recipient_count' => $campaign->recipient_count,
                'sent_count' => $campaign->sent_count,
                'failed_count' => $campaign->failed_count,
                'skipped_count' => $campaign->skipped_count,
            ],
        ]);
    }

    public function cancel(MemberNewsletterCampaign $campaign): RedirectResponse
    {
        try {
            $skipped = $this->sender->cancelRemaining($campaign);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.newsletter.campaigns.show', $campaign)
            ->with('success', "Send cancelled. {$skipped} pending emails were skipped.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCampaign(Request $request): array
    {
        return $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'preview_text' => ['nullable', 'string', 'max:255'],
            'body_html' => ['required', 'string', 'max:50000'],
        ]);
    }
}

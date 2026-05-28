<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoryTimelineEntry;
use App\Services\HistoryImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HistoryTimelineController extends Controller
{
    public function __construct(
        private readonly HistoryImageStorage $historyImages,
    ) {}

    public function index(): View
    {
        $entries = HistoryTimelineEntry::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.history-timeline.index', [
            'entries' => $entries,
        ]);
    }

    public function create(): View
    {
        return view('admin.history-timeline.create', [
            'entry' => new HistoryTimelineEntry([
                'sort_order' => (HistoryTimelineEntry::query()->max('sort_order') ?? 0) + 1,
                'is_published' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEntry($request, null);

        if ($request->hasFile('image_file')) {
            $validated['image'] = $this->historyImages->store(
                $request->file('image_file'),
                $validated['slug']
            );
        }

        unset($validated['image_file'], $validated['remove_image']);
        HistoryTimelineEntry::query()->create($validated);

        return redirect()
            ->route('admin.history-timeline.index')
            ->with('success', 'History timeline entry added.');
    }

    public function edit(HistoryTimelineEntry $historyTimeline): View
    {
        return view('admin.history-timeline.edit', [
            'entry' => $historyTimeline,
        ]);
    }

    public function update(Request $request, HistoryTimelineEntry $historyTimeline): RedirectResponse
    {
        $validated = $this->validateEntry($request, $historyTimeline);

        if ($request->boolean('remove_image')) {
            $this->historyImages->delete($historyTimeline->image);
            $validated['image'] = null;
        } elseif ($request->hasFile('image_file')) {
            $this->historyImages->delete($historyTimeline->image);
            $validated['image'] = $this->historyImages->store(
                $request->file('image_file'),
                $historyTimeline->slug
            );
        } else {
            unset($validated['image']);
        }

        unset($validated['image_file'], $validated['remove_image']);
        $historyTimeline->update($validated);

        return redirect()
            ->route('admin.history-timeline.index')
            ->with('success', 'History timeline entry updated.');
    }

    public function destroy(HistoryTimelineEntry $historyTimeline): RedirectResponse
    {
        $this->historyImages->delete($historyTimeline->image);
        $historyTimeline->delete();

        return redirect()
            ->route('admin.history-timeline.index')
            ->with('success', 'History timeline entry removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEntry(Request $request, ?HistoryTimelineEntry $existing): array
    {
        $slugRule = Rule::unique('history_timeline_entries', 'slug');
        if ($existing) {
            $slugRule = $slugRule->ignore($existing->id);
        }

        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slugRule],
            'sort_order' => ['required', 'integer', 'min:1', 'max:9999'],
            'label' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'image_file' => ['nullable', 'image', 'max:8192'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
        ], [], [
            'slug' => 'URL id',
            'sort_order' => 'display order',
            'label' => 'timeline label',
            'title' => 'story headline',
            'body' => 'story text',
            'alt' => 'image description',
            'caption' => 'image caption',
            'image_file' => 'timeline image',
        ]);

        $validated['slug'] = Str::slug($validated['slug'], '-');
        $validated['is_published'] = $request->boolean('is_published');
        $validated['alt'] = isset($validated['alt']) ? trim($validated['alt']) : null;
        $validated['caption'] = isset($validated['caption']) ? trim($validated['caption']) : null;
        if ($validated['alt'] === '') {
            $validated['alt'] = null;
        }
        if ($validated['caption'] === '') {
            $validated['caption'] = null;
        }

        return $validated;
    }
}

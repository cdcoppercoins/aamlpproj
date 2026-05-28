<?php

namespace App\Http\Controllers;

use App\Models\HistoryTimelineEntry;
use App\Support\HistoryTimelineConfigImporter;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        $entries = HistoryTimelineEntry::query()->publishedOrdered()->get();

        if ($entries->isEmpty()) {
            HistoryTimelineConfigImporter::importIfEmpty();
            $entries = HistoryTimelineEntry::query()->publishedOrdered()->get();
        }

        if ($entries->isEmpty()) {
            $timelineEntries = collect(config('history_timeline', []))
                ->map(function (array $entry) {
                    $image = isset($entry['image']) ? trim((string) $entry['image']) : null;
                    $image = $image !== '' ? $image : null;

                    return [
                        'id' => $entry['id'],
                        'label' => $entry['label'],
                        'title' => $entry['title'],
                        'body' => $entry['body'],
                        'image_url' => $image ? asset($image) : null,
                        'alt' => $entry['alt'] ?? $entry['title'],
                        'caption' => isset($entry['caption']) ? trim((string) $entry['caption']) : null,
                    ];
                })
                ->values()
                ->all();
        } else {
            $timelineEntries = $entries
                ->map(fn (HistoryTimelineEntry $entry) => $entry->toTimelineArray())
                ->all();
        }

        return view('history', [
            'timelineEntries' => $timelineEntries,
        ]);
    }
}

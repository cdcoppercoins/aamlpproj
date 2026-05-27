<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        $entries = collect(config('history_timeline', []))
            ->map(function (array $entry) {
                $image = $entry['image'] ?? null;

                return [
                    'id' => $entry['id'],
                    'label' => $entry['label'],
                    'title' => $entry['title'],
                    'body' => $entry['body'],
                    'image_url' => $image ? asset($image) : null,
                    'alt' => $entry['alt'] ?? $entry['title'],
                    'caption' => $entry['caption'] ?? null,
                ];
            })
            ->values()
            ->all();

        return view('history', [
            'timelineEntries' => $entries,
        ]);
    }
}

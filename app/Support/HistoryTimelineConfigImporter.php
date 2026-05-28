<?php

namespace App\Support;

use App\Models\HistoryTimelineEntry;
use Illuminate\Support\Str;

class HistoryTimelineConfigImporter
{
    public static function importIfEmpty(): int
    {
        if (HistoryTimelineEntry::query()->exists()) {
            return 0;
        }

        return self::importFromConfig();
    }

    public static function importFromConfig(): int
    {
        $entries = config('history_timeline', []);
        $slugCounts = [];
        $imported = 0;
        $sortOrder = 0;

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $baseSlug = Str::slug((string) ($entry['id'] ?? ''), '-');
            if ($baseSlug === '') {
                $baseSlug = 'entry-' . ($sortOrder + 1);
            }

            $slugCounts[$baseSlug] = ($slugCounts[$baseSlug] ?? 0) + 1;
            $slug = $slugCounts[$baseSlug] === 1
                ? $baseSlug
                : $baseSlug . '-' . $slugCounts[$baseSlug];

            $image = isset($entry['image']) ? trim((string) $entry['image']) : null;
            $image = $image !== '' ? $image : null;

            $alt = isset($entry['alt']) ? trim((string) $entry['alt']) : null;
            $alt = $alt !== '' ? $alt : null;

            $caption = isset($entry['caption']) ? trim((string) $entry['caption']) : null;
            $caption = $caption !== '' ? $caption : null;

            HistoryTimelineEntry::query()->create([
                'slug' => $slug,
                'sort_order' => ++$sortOrder,
                'label' => (string) ($entry['label'] ?? $slug),
                'title' => (string) ($entry['title'] ?? $slug),
                'body' => (string) ($entry['body'] ?? ''),
                'image' => $image,
                'alt' => $alt,
                'caption' => $caption,
                'is_published' => true,
            ]);

            $imported++;
        }

        return $imported;
    }
}

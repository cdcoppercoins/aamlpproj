<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            $this->entry(route('home'), '1.0', 'weekly'),
            $this->entry(route('gallery'), '0.9', 'weekly'),
            $this->entry(route('search'), '0.9', 'weekly'),
            $this->entry(route('about'), '0.6', 'monthly'),
            $this->entry(route('history'), '0.6', 'monthly'),
            $this->entry(route('contribute'), '0.5', 'monthly'),
        ];

        $sets = DB::table('plates')
            ->select(
                'set_name',
                DB::raw('MAX(updated_at) as lastmod')
            )
            ->groupBy('set_name')
            ->orderBy('set_name')
            ->get();

        foreach ($sets as $set) {
            $urls[] = $this->entry(
                route('gallery.show', $set->set_name),
                '0.8',
                'monthly',
                $set->lastmod
            );
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @return array{loc: string, priority: string, changefreq: string, lastmod: ?string}
     */
    private function entry(string $loc, string $priority, string $changefreq, mixed $lastmod = null): array
    {
        $formattedLastmod = null;

        if ($lastmod !== null) {
            $timestamp = strtotime((string) $lastmod);
            if ($timestamp !== false) {
                $formattedLastmod = date('Y-m-d', $timestamp);
            }
        }

        return [
            'loc' => $loc,
            'priority' => $priority,
            'changefreq' => $changefreq,
            'lastmod' => $formattedLastmod,
        ];
    }
}

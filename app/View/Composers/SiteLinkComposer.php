<?php

namespace App\View\Composers;

use App\Models\SiteLink;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SiteLinkComposer
{
    public function compose(View $view): void
    {
        if (! Schema::hasTable('site_links')) {
            $view->with('siteLinks', collect());

            return;
        }

        try {
            $links = SiteLink::query()
                ->published()
                ->with('page')
                ->ordered()
                ->get()
                ->groupBy('placement');
        } catch (\Throwable $e) {
            report($e);
            $links = collect();
        }

        $view->with('siteLinks', $links);
    }
}
<?php

namespace App\View\Composers;

use App\Models\SiteLink;
use Illuminate\View\View;

class SiteLinkComposer
{
    public function compose(View $view): void
    {
        $links = SiteLink::query()
            ->published()
            ->with('page')
            ->ordered()
            ->get()
            ->groupBy('placement');

        $view->with('siteLinks', $links);
    }
}

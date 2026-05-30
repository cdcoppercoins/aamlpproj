<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\SiteLink;
use Illuminate\Database\Seeder;

class SiteLinkSeeder extends Seeder
{
    public function run(): void
    {
        $about = Page::query()->where('slug', 'about')->first();

        if ($about) {
            SiteLink::query()->updateOrCreate(
                [
                    'placement' => 'header_more',
                    'page_id' => $about->id,
                ],
                [
                    'label' => 'About',
                    'url' => null,
                    'sort_order' => 1,
                    'is_published' => true,
                ]
            );

            SiteLink::query()->updateOrCreate(
                [
                    'placement' => 'footer_left',
                    'page_id' => $about->id,
                ],
                [
                    'label' => 'About',
                    'url' => null,
                    'sort_order' => 1,
                    'is_published' => true,
                ]
            );
        }

        SiteLink::query()->updateOrCreate(
            [
                'placement' => 'footer_left',
                'url' => '/contribute',
            ],
            [
                'label' => 'Contribute',
                'page_id' => null,
                'sort_order' => 2,
                'is_published' => true,
            ]
        );

        SiteLink::query()->updateOrCreate(
            [
                'placement' => 'footer_left',
                'url' => 'https://www.facebook.com/groups/miniplates',
            ],
            [
                'label' => 'Facebook Group',
                'page_id' => null,
                'sort_order' => 3,
                'is_published' => true,
            ]
        );

        $terms = Page::query()->where('slug', 'terms-of-service')->first();

        if ($terms) {
            SiteLink::query()->updateOrCreate(
                [
                    'placement' => 'footer_left',
                    'page_id' => $terms->id,
                ],
                [
                    'label' => 'Terms of Service',
                    'url' => null,
                    'sort_order' => 4,
                    'is_published' => true,
                ]
            );
        }

        $privacy = Page::query()->where('slug', 'privacy-policy')->first();

        if ($privacy) {
            SiteLink::query()->updateOrCreate(
                [
                    'placement' => 'footer_left',
                    'page_id' => $privacy->id,
                ],
                [
                    'label' => 'Privacy Policy',
                    'url' => null,
                    'sort_order' => 5,
                    'is_published' => true,
                ]
            );
        }
    }
}

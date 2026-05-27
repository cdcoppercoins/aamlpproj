<?php

/**
 * Home page hero rotator slides.
 *
 * Managed in Admin → Home banners (/admin/home-hero).
 * This file is the fallback until the database tables exist and
 * after all slides are removed from the admin panel.
 */
return [
    'interval_ms' => 7000,

    'slides' => [
        [
            'image' => 'home_top_banner.jpg',
            'alt' => 'Miniature state license plates from classic premium sets',
            'headline' => 'State & topical sets',
            'subline' => 'Browse plate photos organized by issued set',
            'cta' => 'Browse the Gallery',
            'route' => 'gallery',
            'bg' => 'linear-gradient(135deg, #2d6388 0%, #4079a5 55%, #5a96bc 100%)',
        ],
        [
            'image' => 'blue_back_composite_img_sm.jpg',
            'alt' => '1953 Wheaties cereal miniature license plate set',
            'headline' => 'Cereal box premiums',
            'subline' => 'General Mills, Post, and other breakfast sets from the 1950s',
            'cta' => 'Explore the Gallery',
            'route' => 'gallery',
            'bg' => 'linear-gradient(135deg, #c8862a 0%, #fab95b 45%, #ffe8c4 100%)',
        ],
        [
            'image' => 'home_top_banner.jpg',
            'alt' => 'Miniature license plates for catalog research',
            'headline' => 'Catalog search',
            'subline' => 'Filter by state, year, company, and set type',
            'cta' => 'Search the Catalog',
            'route' => 'search',
            'bg' => 'linear-gradient(135deg, #1e4d66 0%, #356889 50%, #4079a5 100%)',
        ],
        [
            'image' => 'brands.jpg',
            'alt' => 'Brands that issued miniature license plates: Post, Topps, General Mills, Goudey, and others',
            'headline' => 'Classic issuers',
            'subline' => 'Post, Topps, Goudey, Quaker, and dozens more',
            'cta' => 'See all sets',
            'route' => 'gallery',
            'bg' => 'linear-gradient(135deg, #8a5a12 0%, #c8862a 40%, #fab95b 100%)',
        ],
        [
            'image' => 'blue_back_composite_img_sm.jpg',
            'alt' => 'Collector checklist for miniature license plates',
            'headline' => 'Printable checklists',
            'subline' => 'Track what you own and still need for each set',
            'cta' => 'My Collection',
            'route' => 'collection.index',
            'bg' => 'linear-gradient(135deg, #264a62 0%, #4079a5 60%, #6a9fbf 100%)',
        ],
    ],
];

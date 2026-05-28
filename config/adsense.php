<?php

/**
 * Google AdSense — publisher ID and ad unit slot IDs from .env.
 * See docs/ADSENSE_SETUP.md for applying and creating units in AdSense.
 */
return [

    'enabled' => (bool) env('ADSENSE_ENABLED', false),

    /** ca-pub-XXXXXXXXXXXXXXXX (from AdSense → Account → Account information) */
    'client' => env('ADSENSE_CLIENT', ''),

    /** Show ads when APP_ENV=local (default off; use placeholders instead) */
    'show_on_local' => (bool) env('ADSENSE_SHOW_ON_LOCAL', false),

    /** Gray boxes showing where ads will appear (local/staging layout check) */
    'show_placeholders' => (bool) env('ADSENSE_SHOW_PLACEHOLDERS', true),

    /**
     * Ad unit slot IDs (numeric string from AdSense → Ads → By ad unit).
     * Leave blank until each unit is created; placeholders still show placement names.
     */
    'slots' => [
        'below-header' => env('ADSENSE_SLOT_BELOW_HEADER', ''),
        'above-footer' => env('ADSENSE_SLOT_ABOVE_FOOTER', ''),
        'search-mid' => env('ADSENSE_SLOT_SEARCH_MID', ''),
    ],

    /**
     * Where each slot appears (for docs and placeholder labels).
     */
    'placements' => [
        'below-header' => [
            'label' => 'Below navigation (all public pages)',
            'format' => 'horizontal',
            'pages' => 'Site-wide, under session bar / above main content',
        ],
        'above-footer' => [
            'label' => 'Above footer (all public pages)',
            'format' => 'horizontal',
            'pages' => 'Site-wide, above footer links and newsletter',
        ],
        'search-mid' => [
            'label' => 'Mid search results',
            'format' => 'horizontal',
            'pages' => 'Catalog search only, after the first few result cards',
        ],
    ],

];

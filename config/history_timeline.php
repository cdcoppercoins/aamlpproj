<?php

/**
 * History timeline content — one array entry per milestone.
 *
 * Add your paragraphs and images here (or split into a dedicated file later).
 * Images go in: public/history-media/{image filename} only (not public/history/).
 *
 * Fields:
 *   id     — unique slug (letters, numbers, hyphens)
 *   label  — short text on the timeline button (usually a year)
 *   title  — modal headline
 *   body   — paragraph HTML allowed: use plain text or <p>...</p>
 *   image   — path under public/, e.g. history-media/my-photo.jpg (optional)
 *   alt     — image description for accessibility (optional)
 *   caption — short line under the image in the modal, shown in italics (optional)
 */
return [
    [
        'id' => 'goudey-cards',
        'label' => 'Goudey Gum - 1936-1939',
        'title' => 'First License Plate Cards',
        'body' => '<p>The hobby of collecting small things that represent license plates began in 1936 with the first set of
        Auto License Plate trading cards issued by Goudey Gum Co. of Boston, Massachusetts. The very
        successful entry into baseball cards by this chewing gum giant in 1933 helped spark a craze that had no
        match. Goudey capitalized on this craze by expanding into other colorful collectibles, such as license
        plates. The cards came with a generous stick of gum and were sold as penny packs in candy stores.
        Although the idea of placing images of all sorts of subjects on trading cards and placing them in products
        such as candy, gum, and cigarettes was not new, trading cards bearing auto license plates were new.
        Kids from all over, especially from the Northeast where the gum was more widely distributed, bought
        up the gum and, hence, the cards. The challenge was to complete a set of all 36 cards. Their popularity in
        the 1930s speaks for itself in that cards from this era are still readily available, yet can be somewhat pricey,
        especially for top-grade examples that are certified. Auction prices realized indicate values into the
        hundreds of dollars for some of the best-preserved cards.</p>
        <p>Goudey Gum Co. continued this run of license plate cards through 1937, 1938, and 1939. Although the
        number of cards it took to build a complete set changed through these years, the designs were the same
        except for the annual design changes made to closely match the colors used on real license plates for the
        year.</p>',
        'image' => 'history-media/goudey-cards.jpg',
        'alt' => '1936-1939 Goudey License Plate Cards',
        'caption' => 'Very colorful and well printed cards on thick stock make up the Goudey Auto License PLate sets from 1936-1939.',
    ],
    [
        'id' => 'world-wide-gum',
        'label' => 'World Wide Gum - 1939',
        'title' => 'Canada Enters the Picture',
        'body' => '<p>World Wide Gum Co. of Granby, Quebec, a company with a close relationship to Goudey, entered the
market for one year in 1939 with a set of cards that are near-twins of the Goudey cards, except for the
change in company name on the backs of the cards and the fact that the Canadian provinces were
featured more heavily because the cards were distributed mostly in Canada. These cards have quirks that
tend to indicate that they were more of an afterthought than a well-planned set. These quirks are covered
more thoroughly in the listing for this set in the catalogue on this website.</p>',
        'image' => 'history-media/worldwide-gum.jpg',
        'alt' => '1939 World Wide Gum of Canada has a different back from Goudey.',
        'caption' => 'The company name on the back of World Wide Gum is a way to tell the difference between those and Goudey cards.',
    ],
    [
        'id' => 'sample-1970s',
        'label' => '1970s',
        'title' => 'Peak variety and regional issues',
        'body' => 'Replace with your paragraph covering expansion of sets, regional gas station and food promotions, and the widening range of materials and backs.',
        'image' => null,
        'alt' => null,
        'caption' => null,
    ],
    [
        'id' => 'sample-1980s',
        'label' => '1980s',
        'title' => 'Post and late-era premiums',
        'body' => 'Replace with your paragraph on later Post cereal issues, declining insert formats, and the transition toward collector-driven markets.',
        'image' => null,
        'alt' => null,
        'caption' => null,
    ],
    [
        'id' => 'sample-today',
        'label' => 'Today',
        'title' => 'Collecting and research',
        'body' => 'Replace with your closing paragraph on modern collecting, catalogs, and how enthusiasts document varieties, conditions, and history.',
        'image' => null,
        'alt' => null,
        'caption' => null,
    ],
];

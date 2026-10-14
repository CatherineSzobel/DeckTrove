<?php

use App\Cards\MagicCardMapper;
use App\Cards\YugiohCardMapper;
use App\Decks\MagicTextFormat;
use App\Decks\YdkFormat;
use App\Services\MagicPackService;
use App\Services\MagicService;
use App\Services\YugiohPackService;
use App\Services\YugiohService;

/**
 * Supported card series. To add one, follow docs/adding-a-series.md; tests/Feature/SeriesContractTest.php
 * checks that every series here is complete.
 *
 * - Each top-level key is a series and is used as the {series} route segment.
 * - `provider` fetches raw cards, `mapper` reads them into the fields the app shows.
 * - `deck_format` imports and exports decks in a format other tools understand.
 * - `deck` holds the deck building rules, which are enforced by the server and passed to the deck builder JS.
 *
 * Only plain values belong here (no closures), so the config can be cached in production.
 */
return [

    'yugioh' => [
        'label' => 'Yu-Gi-Oh!',
        'logo' => 'yugioh.png', // in resources/img
        'tagline' => 'Duel your way to victory!',
        'related_label' => 'More from this archetype',
        'provider' => YugiohService::class,
        'mapper' => YugiohCardMapper::class,
        'deck_format' => YdkFormat::class,
        'link_prefix' => '/yugioh',

        // Order matters: it is the order the filter dropdowns are rendered in.
        'filters' => [
            'type' => ['label' => 'Type'],
            'attribute' => ['label' => 'Attribute'],
            'race' => ['label' => 'Race'],
            'archetype' => ['label' => 'Archetype'],
        ],

        'deck' => [
            'zones' => [
                'main' => ['label' => 'Main Deck', 'min' => 40, 'max' => 60],
                'extra' => ['label' => 'Extra Deck', 'min' => 0, 'max' => 15],
                'side' => ['label' => 'Side Deck', 'min' => 0, 'max' => 15],
            ],
            'max_copies' => 3,
            // Card types (substring of the card's type) that belong in the extra deck.
            'extra_types' => ['Fusion', 'Synchro', 'XYZ', 'Link'],
            // Card types that are exempt from the copy limit.
            'unlimited_types' => [],
            // Formats a deck can be built for. Keys are what the mapper's copyLimit() looks up; no format = Casual.
            'formats' => [
                'tcg' => ['label' => 'TCG Advanced'],
                'ocg' => ['label' => 'OCG'],
            ],
            // The game's own words for a format limit (copies allowed => label), shown in the deck builder.
            'limit_labels' => [0 => 'Forbidden', 1 => 'Limited', 2 => 'Semi-Limited'],
            // Deck stats groups: the keys the mapper's statFacets() returns. `order` lists values to show first,
            // in that order; other values follow in natural order. `colors` (optional) colours the deck page charts.
            'stats' => [
                'category' => [
                    'label' => 'Card types',
                    'order' => ['Monster', 'Spell', 'Trap'],
                    'colors' => ['Monster' => '#B8652F', 'Spell' => '#1C8C7E', 'Trap' => '#A63D7E'],
                ],
                'level' => ['label' => 'Level / Rank'],
                'attribute' => ['label' => 'Attribute'],
            ],
        ],

        'pack' => [
            'provider' => YugiohPackService::class,
            'link_prefix' => '/yugioh',
            'code' => 'set_code',
            'name' => 'set_name',
            'slug' => 'slug',
            'release_date' => 'tcg_date',
            'card_count' => 'num_of_cards',
            'image' => 'set_image',
            'cover' => 'art', // product images; 'symbol' for set symbols shown on a tile
        ],
    ],

    'magic' => [
        'label' => 'Magic: The Gathering',
        'logo' => 'magic.png',
        'tagline' => 'Cast spells and control the battlefield!',
        'related_label' => 'More from this set',
        'provider' => MagicService::class,
        'mapper' => MagicCardMapper::class,
        'deck_format' => MagicTextFormat::class,
        'link_prefix' => '/magic',

        'filters' => [
            'type' => ['label' => 'Type'],
            'color' => ['label' => 'Color', 'labels' => ['W' => 'White', 'U' => 'Blue', 'B' => 'Black', 'R' => 'Red', 'G' => 'Green', 'C' => 'Colorless']],
            'rarity' => ['label' => 'Rarity'],
            'set_name' => ['label' => 'Set'],
        ],

        'deck' => [
            'zones' => [
                'main' => ['label' => 'Main Deck', 'min' => 60, 'max' => 100],
                'side' => ['label' => 'Sideboard', 'min' => 0, 'max' => 15],
            ],
            'max_copies' => 4,
            'extra_types' => [],
            'unlimited_types' => ['Basic Land'],
            // Commander needs singleton rules and a commander zone, so it isn't listed yet.
            'formats' => [
                'standard' => ['label' => 'Standard'],
                'pioneer' => ['label' => 'Pioneer'],
                'modern' => ['label' => 'Modern'],
                'legacy' => ['label' => 'Legacy'],
                'vintage' => ['label' => 'Vintage'],
                'pauper' => ['label' => 'Pauper'],
            ],
            'limit_labels' => [0 => 'Not legal', 1 => 'Restricted'],
            'stats' => [
                'type' => [
                    'label' => 'Type',
                    'order' => ['Creature', 'Planeswalker', 'Battle', 'Instant', 'Sorcery', 'Artifact', 'Enchantment', 'Land'],
                    'colors' => [
                        'Creature' => '#3E7A4A', 'Planeswalker' => '#8A5A9E', 'Battle' => '#9C6B3A', 'Instant' => '#2F6DB0',
                        'Sorcery' => '#B5432F', 'Artifact' => '#6B7280', 'Enchantment' => '#9C8A5A', 'Land' => '#7A5C3A',
                    ],
                ],
                'mana_value' => ['label' => 'Mana curve', 'order' => ['0', '1', '2', '3', '4', '5', '6', '7+']],
                'color' => [
                    'label' => 'Color',
                    'order' => ['White', 'Blue', 'Black', 'Red', 'Green', 'Multicolor', 'Colorless'],
                    'colors' => [
                        'White' => '#C9B98A', 'Blue' => '#2F6DB0', 'Black' => '#4A443F', 'Red' => '#B5432F',
                        'Green' => '#3E7A4A', 'Multicolor' => '#B8902F', 'Colorless' => '#857E76',
                    ],
                ],
            ],
        ],

        'pack' => [
            'provider' => MagicPackService::class,
            'link_prefix' => '/magic',
            'code' => 'code',
            'name' => 'name',
            'type' => 'set_type',
            'release_date' => 'released_at',
            'card_count' => 'card_count',
            'image' => 'icon_svg_uri',
            'cover' => 'symbol',
        ],
    ],

];

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
 * Supported card series.
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
        'provider' => YugiohService::class,
        'mapper' => YugiohCardMapper::class,
        'deck_format' => YdkFormat::class,
        'link_prefix' => '/yugioh',

        'rarity_colors' => [
            'common' => 'text-gray-600',
            'rare' => 'text-yellow-600',
            'super' => 'text-green-700',
            'ultra' => 'text-orange-600',
            'secret' => 'text-purple-600',
        ],

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
        ],
    ],

    'magic' => [
        'label' => 'Magic: The Gathering',
        'provider' => MagicService::class,
        'mapper' => MagicCardMapper::class,
        'deck_format' => MagicTextFormat::class,
        'link_prefix' => '/magic',

        'rarity_colors' => [
            'common' => 'text-gray-600',
            'uncommon' => 'text-green-700',
            'rare' => 'text-yellow-600',
            'mythic' => 'text-orange-600',
            'special' => 'text-purple-600',
            'bonus' => 'text-blue-600',
        ],

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
        ],
    ],

];

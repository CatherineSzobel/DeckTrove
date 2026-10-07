<?php

use App\Services\MagicPackService;
use App\Services\MagicService;
use App\Services\YugiohPackService;
use App\Services\YugiohService;

/**
 * Config mapping external card APIs to internal app fields.
 *
 * - Each top-level key is a supported series and is used as the {series} route segment.
 * - Field values are either dot-paths into the raw card array or callables receiving it.
 * - `deck` holds the deck building rules, which are enforced by the server and passed to the deck builder JS.
 */
$placeholder = 'https://placehold.co/200x280?text=No+Image';

// Double-faced Magic cards keep most fields per face instead of at the top level.
$magicFaces = fn (array $card, string $field) => data_get($card, $field)
    ?? collect(data_get($card, 'card_faces', []))->pluck($field)->filter()->implode(' // ');

return [

    'yugioh' => [
        'label' => 'Yu-Gi-Oh!',
        'provider' => YugiohService::class,
        'link_prefix' => '/yugioh',
        'image' => fn ($card) => data_get($card, 'card_images.0.image_url', $placeholder),
        'image_small' => fn ($card) => data_get($card, 'card_images.0.image_url_small', $placeholder),
        'type_field' => fn ($card) => data_get($card, 'type', ''),
        'subtype_field' => 'race',

        'stats' => fn (array $card) => [
            'left' => ['label' => 'ATK', 'value' => $card['atk'] ?? null],
            'right' => str_contains(strtolower($card['type'] ?? ''), 'link')
                ? ['label' => 'LINK', 'value' => $card['linkval'] ?? null]
                : ['label' => 'DEF', 'value' => $card['def'] ?? null],
        ],

        'description' => fn ($card) => data_get($card, 'desc', ''),

        'set_field' => 'card_sets.0.set_name',
        'rarity_field' => 'card_sets.0.set_rarity',
        'price_field' => 'card_prices.0.cardmarket_price',

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

        'print_sets' => fn ($card) => collect(data_get($card, 'card_sets', []))->map(fn ($set) => [
            'set_code' => $set['set_code'] ?? '',
            'set_name' => $set['set_name'] ?? 'Unknown',
        ])->toArray(),

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
            'release_date' => 'tcg_date',
            'card_count' => 'num_of_cards',
            'image' => 'set_image',
        ],
    ],

    'magic' => [
        'label' => 'Magic: The Gathering',
        'provider' => MagicService::class,
        'link_prefix' => '/magic',

        'image' => fn ($card) => data_get($card, 'image_uris.normal')
            ?? data_get($card, 'card_faces.0.image_uris.normal')
            ?? $placeholder,
        'image_small' => fn ($card) => data_get($card, 'image_uris.small')
            ?? data_get($card, 'card_faces.0.image_uris.small')
            ?? $placeholder,
        // Every face image, used by the "Transform" toggle on double-faced cards.
        'face_images' => fn ($card) => collect(data_get($card, 'card_faces', []))
            ->pluck('image_uris.normal')->filter()->values()->all(),

        'type_field' => fn ($card) => $magicFaces($card, 'mana_cost'),
        'subtype_field' => 'type_line',

        'stats' => fn (array $card) => [
            'left' => ['label' => 'Power', 'value' => $card['power'] ?? data_get($card, 'card_faces.0.power')],
            'right' => ['label' => 'Toughness', 'value' => $card['toughness'] ?? data_get($card, 'card_faces.0.toughness')],
        ],

        'description' => fn ($card) => $magicFaces($card, 'oracle_text'),

        'set_field' => 'set_name',
        'rarity_field' => 'rarity',
        'price_field' => 'prices.usd',

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

        'print_sets' => fn ($card) => [
            [
                'set_code' => data_get($card, 'set') ?? '',
                'set_name' => data_get($card, 'set_name') ?? 'Unknown',
            ],
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

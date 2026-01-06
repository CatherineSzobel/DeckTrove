<?php
/**
 * Config mapping external card APIs to internal app fields
 *
 * - All fields should resolve to app-level concepts
 * - Values may be string paths or callables
 */
return [

    'yugioh' => [
        'link_prefix' => '/yugioh',
        'image' => fn($card) => data_get(
            $card,
            'card_images.0.image_url',
            'https://via.placeholder.com/200x280?text=No+Image'
        ),
        'type_field' =>  fn($card) => data_get($card, 'type', ''),
        'subtype_field' => 'race',

        'stats' => fn(array $card) => str_contains(
            strtolower($card['type'] ?? ''),
            'link'
        )
            ? [
                'left' => [
                    'label' => 'ATK',
                    'value' => $card['atk'] ?? null,
                ],
                'right' => [
                    'label' => 'LINK',
                    'value' => $card['linkval'] ?? null,
                ],
            ]
            : [
                'left' => [
                    'label' => 'ATK',
                    'value' => $card['atk'] ?? null,
                ],
                'right' => [
                    'label' => 'DEF',
                    'value' => $card['def'] ?? null,
                ],
            ],

        'description' => fn($card) => data_get($card, 'desc', ''),

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
        'filter_fields' => ['search', 'type', 'attribute', 'race', 'archetype'],

        'print_sets' => fn($card) => collect(data_get($card, 'card_sets', []))->map(fn($set) => [
            'set_code' => $set['set_code'] ?? '',
            'set_name' => $set['set_name'] ?? 'Unknown',
        ])->toArray(),

        'pack' => [
            'link_prefix' => '/yugioh',
            'code' => 'set_code',
            'name' => 'set_name',
            'release_date' => 'tcg_date',
            'card_count' => 'num_of_cards',
            'image' => 'set_image',
        ],
    ],

    'magic' => [
        'link_prefix' => '/magic',

        'image' => fn($card) => data_get($card, 'image_uris.normal')
            ?? data_get($card, 'card_faces.0.image_uris.normal')
            ?? 'https://via.placeholder.com/200x280?text=No+Image',

        'type_field' => fn($card) =>
        data_get($card, 'mana_cost', '') ??
            data_get($card, 'card_faces.0.mana_cost', ''),

        'subtype_field' => 'type_line',
        'stats' => fn(array $card) => [
            'left' => [
                'label' => 'Power',
                'value' => $card['power'] ?? null,
            ],
            'right' => [
                'label' => 'Toughness',
                'value' => $card['toughness'] ?? null,
            ],
        ],

        'description' => fn($card) =>
        data_get($card, 'oracle_text', '') ??
            data_get($card, 'card_faces.0.oracle_text', ''),

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

        'filter_fields' => ['search', 'type', 'color', 'rarity', 'set_name'],

        'print_sets' => fn($card) => [
            [
                'set_code' => data_get($card, 'set') ?? '',
                'set_name' => data_get($card, 'set_name') ?? 'Unknown',
            ]
        ],

        'pack' => [
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

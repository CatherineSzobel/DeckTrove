<?php

return [

    'yugioh' => [
        'link_prefix' => '/yugioh/card/',
        'image' => fn($card) => data_get($card, 'card_images.0.image_url', 'https://via.placeholder.com/200x280?text=No+Image'),
        'type_field' =>  fn($card) => data_get($card, 'type', ''),
        'subtype_field' => 'race',
        'atk_def' => ['atk', 'def'],
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
    ],

    'magic' => [
        'link_prefix' => '/magic/card/',
        'image' => fn($card) => data_get($card, 'image_uris.normal')
            ?? data_get($card, 'card_faces.0.image_uris.normal')
            ?? 'https://via.placeholder.com/200x280?text=No+Image',
        'type_field' => fn($card) => data_get($card, 'mana_cost', ''),
        'colorless_type' => fn($card) => data_get($card, 'card_faces.0.mana_cost', ''),
        'subtype_field' => 'type_line',
        'atk_def' => ['power', 'toughness'],
        'description' => fn($card) => data_get($card, 'oracle_text', ''),
        'colorless_description' => fn($card) => data_get($card, 'card_faces.0.oracle_text', ''),
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
    ],

];

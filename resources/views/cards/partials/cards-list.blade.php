{{-- resources/views/components/cards-table.blade.php --}}
@props(['cards' => [], 'series' => 'magic'])

<div class="overflow-x-auto">
    <x-card-list-table :headers="['Name', 'ATK/Power', 'DEF/Toughness', 'Description', 'Type', 'Rarity', 'Set', 'Price']">
        @foreach($cards as $card)
            @php
                // Name
                $name = $card['name'] ?? 'Unknown';

                // ATK / DEF or Power / Toughness
                [$atkField, $defField] = $seriesConfig['atk_def'] ?? [null, null];
                $atk = $atkField ? data_get($card, $atkField) : '-';
                $def = $defField ? data_get($card, $defField) : '-';
                $atkDef = ($atk !== null || $def !== null) ? "$atk/$def" : '-';

                // Description (handle colorless / card_faces if applicable)
                $desc = isset($card['card_faces']) && is_array($card['card_faces']) && count($card['card_faces']) > 0
                    ? ($seriesConfig['colorless_description'] ?? fn($c) => '')($card)
                    : ($seriesConfig['description'] ?? fn($c) => '')($card);
                $descFormatted = nl2br(e($desc)) ?: '-';

                // Type / Subtype
                $type = data_get($card, $seriesConfig['subtype_field'] ?? '') ?? '-';

                // Set
                $set = data_get($card, $seriesConfig['set_field'] ?? '') ?? '-';

                // Rarity
                $rarityRaw = strtolower(data_get($card, $seriesConfig['rarity_field'] ?? '', 'unknown'));
                $rarityColor = $seriesConfig['rarity_colors'][$rarityRaw] ?? 'text-gray-500';
                $rarity = ucfirst($rarityRaw);

                // Price
                $price = data_get($card, $seriesConfig['price_field'] ?? '') ?? 'N/A';

                // URL prefix
                $urlPrefix = $seriesConfig['link_prefix'] ?? '/';
                $cardId = $card['id'] ?? null;
            @endphp

            <x-card-list-table-content
                :name="$name"
                :contents="[$atkDef, $descFormatted, $type, $rarity, $set, '$'.$price]"
                :urlPrefix="$urlPrefix"
                :cardId="$cardId"
            />
        @endforeach
    </x-card-list-table>
</div>

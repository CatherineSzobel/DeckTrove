@props(['cards' => [], 'series' => 'magic'])

@php
$seriesLower = strtolower($series);

switch ($seriesLower) {
    case 'yugioh':
        $statHeader = 'ATK / DEF';
        break;
    case 'magic':
        $statHeader = 'Power / Toughness';
        break;
    default:
        $statHeader = 'Stats';
}
@endphp

<div class="overflow-x-auto">
    <x-card-list-table :headers="['Name', 'Description', 'Type', 'Rarity', 'Set', 'Price']">
        @foreach($cards as $card)
            <x-card-list-table-content
                :name="$card->name()"
                :contents="[

                        nl2br(e($card->description())) ?: '-',
                        $card->type() ?: '-',
                        $card->rarityLabel(),
                        $card->setName(),
                        '$'.$card->price()
                    ]"
                :urlPrefix="$card->link()"
                :cardId="$card->id() ?? null" />
        @endforeach
    </x-card-list-table>
</div>

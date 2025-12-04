{{-- resources/views/components/cards-table.blade.php --}}
@props(['cards' => []])

<div class="overflow-x-auto">
    <x-card-list-table :headers="['Name', 'cmc', 'Oracle Text', 'Mana Cost', 'Type', 'Rarity', 'Set', 'Price']">
        @foreach($cards as $card)
            <x-card-list-table-content :name="$card['name'] ?? 'Unknown'" :contents="[
                $card['cmc'] ?? 'Unknown',
                !empty($card['oracle_text']) ? nl2br(e($card['oracle_text'])) : 'Unknown',
                $card['mana_cost'] ?? '-',
                $card['type_line'] ?? '-',
                $card['rarity'] ?? '-',
                $card['set_name'] ?? 'Unknown',
                ($card['prices']['usd'] ?? 'Unknown') . '$',]"
                :urlPrefix="'/magic/card/'"
                :cardId="$card['id']"/>
        @endforeach
    </x-card-list-table>
</div>
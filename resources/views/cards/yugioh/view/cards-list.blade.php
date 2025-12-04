{{-- resources/views/components/cards-table.blade.php --}}
@props(['cards' => []])

<div class="overflow-x-auto">
    <x-card-list-table :headers="['Name', 'Type', 'Race', 'Attribute', 'ATK', 'DEF', 'Price']">
        @foreach($cards as $card)
            <x-card-list-table-content :name="$card['name'] ?? 'Unknown'" :contents="[
                $card['type'] ?? '-',
                $card['race'] ?? '-',
                $card['attribute'] ?? '-',    
                $card['atk'] ?? '-',
                $card['def'] ?? '-',
                $card['card_prices'][0]['cardmarket_price'] ?? '-',]"
                :urlPrefix="'/yugioh/card/'"
                :cardId="$card['id']"  />
        @endforeach
    </x-card-list-table>
</div>
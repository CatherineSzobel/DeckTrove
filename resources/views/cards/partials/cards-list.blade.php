<x-card-list-table :headers="['Name', 'Description', 'Type', 'Rarity', 'Set', 'Price']">
    @foreach ($cards as $card)
    <x-card-list-table-content :card="$card" />
    @endforeach
</x-card-list-table>

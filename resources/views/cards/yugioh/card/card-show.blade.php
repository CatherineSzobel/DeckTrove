<img src="{{ $card->card_images[0]->image_url_small ?? '' }}" alt="{{ $card->name }}">
<div id="card-info">

    <h1 class="text-2xl font-bold mb-4">{{ $card->name }}</h1>
    <p>Type: {{ $card->type ?? 'Unknown' }} - {{ $card->race ?? 'Unknown' }}</p>
    <hr class="my-2 border-gray-300">
    <p>ATK: {{ $card->atk ?? '-' }} | DEF: {{ $card->def ?? '-' }}</p>
    <p>Price: {{ $card->card_prices[0]->cardmarket_price ?? 'Unknown' }}</p>

    <p>Description: {{ $card->desc ?? 'No description available' }}</p>

    <h3>Prints:</h3>

    @if(!empty($card->card_sets))
    @php
    // Map card sets to pack codes (trim after dash) and ensure unique packs
    $uniqueSets = collect($card->card_sets)
    ->map(function($set) {
    $setCodeRaw = $set->set_code ?? '';
    $packCode = explode('-', $setCodeRaw)[0] ?? $setCodeRaw;
    return (object) [
    'pack_code' => $packCode,
    'set_name' => $set->set_name ?? 'Unknown'
    ];
    })
    ->unique('pack_code');
    @endphp

    @foreach($uniqueSets as $set)
    @if($set->pack_code)
    <a href="{{ url('/yugioh/pack/' . urlencode($set->pack_code)) }}"
        class="text-blue-500 underline hover:text-blue-600">
        <p>{{ $set->set_name }}</p>
    </a>
    @else
    <p>{{ $set->set_name }}</p>
    @endif
    @endforeach
    @else
    <p>No prints available</p>
    @endif

</div>
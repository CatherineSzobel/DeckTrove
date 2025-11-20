{{-- IMAGE (handles normal cards + double-faced cards) --}}
@if(isset($card['image_uris']))
<img src="{{ $card['image_uris']['normal'] }}" alt="{{ $card['name'] }}">
@elseif(isset($card['card_faces'][0]['image_uris']))
<img src="{{ $card['card_faces'][0]['image_uris']['normal'] }}" alt="{{ $card['name'] }}">
@else
<img src="" alt="No image available">
@endif


<div id="card-info">
    <h1 class="text-2xl font-bold mb-4">{{ $card['name'] }}</h1>

    {{-- TYPE LINE --}}
    <p>{{ $card['type_line'] ?? 'Unknown Type' }}</p>

    <hr class="my-2 border-gray-300">

    {{-- PRICE --}}
    <p>
        Price:
        {{ $card['prices']['usd'] ?? $card['prices']['eur'] ?? 'Unknown' }}
    </p>

    {{-- ORACLE TEXT --}}
    <p class="mt-2">
        Description: {!! nl2br(e($card['oracle_text'] ?? 'No description available')) !!}
    </p>

    {{-- SET PRINTS --}}
    <h3 class="mt-4 font-semibold">Printings:</h3>

    @if(isset($prints) && count($prints))
    @foreach($prints as $set)
    <a href="{{ url('/magic/pack/' . urlencode($set['set'])) }}"
        class="text-blue-500 underline hover:text-blue-600">
        <p>{{ $set['set_name'] ?? 'Unknown' }}</p>
    </a>
    @endforeach
    @else
    <p>No print data loaded</p>
    @endif

</div>
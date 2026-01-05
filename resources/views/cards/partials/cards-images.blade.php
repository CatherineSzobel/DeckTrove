<div class="relative grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 p-2">
    @foreach($cards as $card)

    @php
    $image = ($seriesConfig['image'] ?? fn($c) => '')($card);

    $desc = isset($card['card_faces']) && is_array($card['card_faces']) && count($card['card_faces']) > 0
    ? ($seriesConfig['colorless_description'] ?? fn($c) => '')($card)
    : ($seriesConfig['description'] ?? fn($c) => '')($card);

    [$atkField, $defField] = $seriesConfig['atk_def'] ?? [null, null];
    $atk = $atkField ? data_get($card, $atkField) : null;
    $def = $defField ? data_get($card, $defField) : null;

    $name = $card['name'] ?? 'Unknown Card';
    $type = data_get($card, $seriesConfig['subtype_field'] ?? '') ?? 'unknown';

    $link = url(($seriesConfig['link_prefix'] . '/card/' ?? '/') . ($card['id'] ?? ''));
    @endphp

    <x-card :desc="$desc">

        <a href="{{ $link }}">
            <img src="{{ $image }}"
                alt="{{ $name }}"
                class="max-w-full max-h-64 w-auto h-auto object-contain rounded">
        </a>

        <x-card-hover-overlay
            :name="$name"
            :underTitle="$type"
            :url="$link">

            @if($atk !== null && $def !== null)
            <p class="text-[10px] mt-1">
                {{ $series === 'magic' ? 'Power' : 'ATK' }}: {{ $atk }} |
                {{ $series === 'magic' ? 'Toughness' : 'DEF' }}: {{ $def }}
            </p>
            @endif

        </x-card-hover-overlay>
    </x-card>

    @endforeach
</div>
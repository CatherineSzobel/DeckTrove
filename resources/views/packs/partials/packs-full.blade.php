<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mx-auto w-full max-w-6xl">
    @foreach($packs as $set)
    @php
    $packConfig = $seriesConfig['pack'] ?? [];

    $code = data_get($set, $packConfig['code'] ?? 'none');
    $name = data_get($set, $packConfig['name'] ?? 'name');
    $release = data_get($set, $packConfig['release_date'] ?? '-');
    $cardCount = data_get($set, $packConfig['card_count'] ?? '-');
    $type = data_get($set, $packConfig['type'] ?? '-');
    $image = data_get($set, $packConfig['image'] ?? null);
    @endphp

    <div class="bg-slate-800 rounded-lg border border-slate-700 p-4 hover:shadow-lg transition">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                @if($image)
                <img src="{{ $image }}" alt="{{ $name }}" class="w-12 h-12 rounded" />
                @else
                <div class="w-12 h-12 bg-slate-700 rounded flex items-center justify-center text-slate-400">
                    {{ strtoupper(substr($series, 0, 2)) }}
                </div>
                @endif
            </div>

            <div class="flex-1">
                <a href="{{ url($seriesConfig['link_prefix'] . '/pack/' . urlencode($code)) }}"
                    class="text-white font-semibold text-lg hover:text-amber-400">{{ $name }}
                {{ !empty($code) ? ' (' . $code . ')' : $code ?? " - no code" }}</a>
                <div class="text-slate-500 text-sm mt-1">Released: {{ $release }}</div>
            </div>

            <div class="text-right flex-shrink-0">
                <div class="text-amber-400 font-bold text-lg">{{ $cardCount }}</div>
                <a href="{{ url($seriesConfig['link_prefix'] . '/pack/' . urlencode($code)) }}"
                    class="inline-block mt-3 bg-amber-500 text-slate-900 px-3 py-1 rounded text-sm font-semibold">View</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
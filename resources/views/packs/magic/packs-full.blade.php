<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mx-auto w-full max-w-6xl">
    @foreach($packs as $set)
    <div class="bg-slate-800 rounded-lg border border-slate-700 p-4 hover:shadow-lg transition">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                @if(!empty($set['icon_svg_uri']))
                <img src="{{ $set['icon_svg_uri'] }}" alt="{{ $set['name'] }}" class="w-12 h-12 rounded" />
                @else
                <div class="w-12 h-12 bg-slate-700 rounded flex items-center justify-center text-slate-400">MC</div>
                @endif
            </div>

            <div class="flex-1">
                <a href="{{ url('/magic/pack/' . urlencode($set['code'])) }}" class="text-white font-semibold text-lg hover:text-amber-400">{{ $set['name'] }}</a>
                <div class="text-slate-400 text-sm mt-1">{{ strtoupper($set['code'] ?? '-') }} • {{ str_replace('_',' ',$set['set_type'] ?? '-') }}</div>
                <div class="text-slate-500 text-sm mt-1">Released: {{ $set['released_at'] ?? '-' }}</div>
            </div>

            <div class="text-right flex-shrink-0">
                <div class="text-amber-400 font-bold text-lg">{{ $set['card_count'] ?? '-' }}</div>
                <a href="{{ url('/magic/pack/' . urlencode($set['code'])) }}" class="inline-block mt-3 bg-amber-500 text-slate-900 px-3 py-1 rounded text-sm font-semibold">View</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
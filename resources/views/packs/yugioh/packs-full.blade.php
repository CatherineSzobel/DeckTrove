<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mx-auto w-full max-w-6xl">
    @foreach($packs as $pack)
    <div class="bg-slate-800 rounded-lg border border-slate-700 p-4 hover:shadow-lg transition">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                @if(!empty($pack['set_image']))
                <img src="{{ $pack['set_image'] }}" alt="{{ $pack['set_name'] }}" class="w-12 h-16 rounded" />
                @else
                <div class="w-12 h-16 bg-slate-700 rounded flex items-center justify-center text-slate-400">YJ</div>
                @endif
            </div>

            <div class="flex-1">
                <a href="{{ url('/yugioh/pack/' . urlencode($pack['set_code'])) }}" class="text-white font-semibold text-lg hover:text-amber-400">{{ $pack['set_name'] }}</a>
                <div class="text-slate-400 text-sm mt-1">{{ strtoupper($pack['set_code'] ?? '-') }} • {{ $pack['tcg_date'] ?? '-' }}</div>
                <div class="text-slate-500 text-sm mt-1">Cards: {{ $pack['num_of_cards'] ?? '-' }}</div>
            </div>

            <div class="text-right flex-shrink-0">
                <a href="{{ url('/yugioh/pack/' . urlencode($pack['set_code'])) }}" class="inline-block mt-3 bg-amber-500 text-slate-900 px-3 py-1 rounded text-sm font-semibold">View</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
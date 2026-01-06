<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mx-auto w-full max-w-6xl">
    @foreach($packs as $pack)

    <div class="bg-slate-800 rounded-lg border border-slate-700 p-4 hover:shadow-lg transition">
        <div class="flex items-start gap-4">

            <div class="flex-shrink-0">
                @if($pack->image())
                <img src="{{ $pack->image() }}"
                    alt="{{ $pack->name() }}"
                    class="w-12 h-12 rounded object-cover" />
                @else
                <div class="w-12 h-12 bg-slate-700 rounded flex items-center justify-center text-slate-400">
                    {{ strtoupper(substr($series, 0, 2)) }}
                </div>
                @endif
            </div>

            <div class="flex-1">
                <a href="{{ $pack->link() }}"
                    class="text-white font-semibold text-lg hover:text-amber-400">
                    {{ $pack->name() }}
                    @if($pack->code())
                    <span class="text-slate-400 text-sm">({{ $pack->code() }})</span>
                    @endif
                </a>

                <div class="text-slate-500 text-sm mt-1">
                    Released: {{ $pack->release() ?? '-' }}
                </div>
            </div>

            <div class="text-right flex-shrink-0">
                <div class="text-amber-400 font-bold text-lg">
                    {{ $pack->cardCount() ?? '-' }}
                </div>

                <a href="{{ $pack->link() }}"
                    class="inline-block mt-3 bg-amber-500 hover:bg-amber-600 text-slate-900 px-3 py-1 rounded text-sm font-semibold">
                    View
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
{{--
    Deck stats sidebar on the deck page, drawn from $charts (App\Decks\StatsCharts): per zone one chart per
    `deck.stats` group, as a stacked bar, columns or bars.
--}}
<aside aria-label="Deck stats" x-data="{ zone: 'main', open: false }"
    class="order-first flex min-w-0 flex-[1_1_300px] flex-col gap-4 rounded-[18px] border border-line bg-surface p-5 lg:sticky lg:top-6 lg:order-none">
    <div class="flex items-center justify-between gap-3">
        <h2 class="heading-display text-[26px] text-ink">Deck stats</h2>
        <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="deck-stats-body"
            class="focus-ring flex h-11 items-center gap-1 rounded-[10px] px-3 text-sm font-bold text-accent-ink lg:hidden">
            <span x-text="open ? 'Hide' : 'Show'">Show</span>
        </button>
    </div>

    <div id="deck-stats-body" :class="open ? 'flex' : 'hidden lg:flex'" class="hidden flex-col gap-5 lg:flex">
        <div role="group" aria-label="Zone" class="flex gap-1 rounded-xl border border-line bg-canvas p-1">
            @foreach ($charts->zones() as $zone)
            <button type="button" @click="zone = @js($zone['key'])" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" :aria-pressed="(zone === @js($zone['key'])).toString()"
                class="segment flex-1 px-2">{{ $zone['tab'] }}</button>
            @endforeach
        </div>

        @foreach ($charts->zones() as $zone)
        <div x-show="zone === @js($zone['key'])" @if (! $loop->first) x-cloak @endif class="flex flex-col gap-5">
            @if ($zone['count'] === 0)
            <p class="px-2 py-6 text-center text-sm text-muted">No cards in the {{ $zone['label'] }}.</p>
            @else
            @foreach ($zone['charts'] as $chart)
            <div class="flex flex-col gap-2">
                <h3 class="font-mono text-xs uppercase tracking-[0.08em] text-muted">{{ $chart['label'] }}</h3>

                @if (! $chart['values'])
                <p class="text-sm text-muted">None</p>
                @elseif ($chart['kind'] === 'stacked')
                <div class="flex h-3.5 gap-0.5 overflow-hidden rounded-[5px]" aria-hidden="true">
                    @foreach ($chart['values'] as $value)
                    <span style="flex-grow: {{ $value['count'] }}; background-color: {{ $value['colour'] }}"></span>
                    @endforeach
                </div>
                <ul class="flex flex-wrap gap-x-3.5 gap-y-1.5 text-[13px] text-body">
                    @foreach ($chart['values'] as $value)
                    <li class="inline-flex items-center gap-1.5">
                        <span aria-hidden="true" class="h-2.5 w-2.5 rounded-[3px]" style="background-color: {{ $value['colour'] }}"></span>
                        {{ $value['value'] }} <b class="font-mono text-ink">{{ $value['count'] }}</b>
                    </li>
                    @endforeach
                </ul>
                @elseif ($chart['kind'] === 'columns')
                <div class="flex h-[120px] items-end gap-1.5 border-b border-line">
                    @foreach ($chart['values'] as $value)
                    <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1" title="{{ $value['value'] }}: {{ $value['count'] }}">
                        <span class="font-mono text-[11px] font-bold text-ink">{{ $value['count'] }}</span>
                        <span class="block w-full rounded-t bg-accent" style="height: {{ $value['percent'] }}%"></span>
                    </div>
                    @endforeach
                </div>
                <div class="flex gap-1.5" aria-hidden="true">
                    @foreach ($chart['values'] as $value)
                    <span class="min-w-0 flex-1 truncate text-center font-mono text-[11px] text-muted">{{ $value['short'] }}</span>
                    @endforeach
                </div>
                <ul class="sr-only">
                    @foreach ($chart['values'] as $value)
                    <li>{{ $value['value'] }}: {{ $value['count'] }}</li>
                    @endforeach
                </ul>
                @else
                <ul class="flex flex-col gap-2">
                    @foreach ($chart['values'] as $value)
                    <li class="flex items-center gap-2.5">
                        <span class="w-20 truncate font-mono text-xs text-body">{{ $value['value'] }}</span>
                        <span aria-hidden="true" class="h-2 flex-1 overflow-hidden rounded-full bg-surface-2">
                            <span class="block h-full rounded-full" style="width: {{ $value['percent'] }}%; background-color: {{ $value['colour'] }}"></span>
                        </span>
                        <span class="w-6 text-right font-mono text-xs font-bold text-ink">{{ $value['count'] }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
            @endif

            <div class="flex justify-between gap-3 border-t border-line pt-3.5 text-[13px] text-muted">
                <span>{{ $zone['label'] }} price</span>
                <span class="font-mono font-bold text-ink">{{ $stats->money($zone['price']) }}</span>
            </div>
        </div>
        @endforeach

        <p class="-mt-2 text-xs text-muted">{{ $charts->priceNote() }}</p>
    </div>
</aside>

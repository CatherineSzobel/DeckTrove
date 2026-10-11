{{--
    Deck stats sidebar on the deck page, drawn from $stats (App\Decks\DeckStatsResult). One chart per `deck.stats`
    group: the first as a stacked bar, mana curve and Level / Rank as columns, the rest as bars.
--}}
@php
    $groups = $series['deck']['stats'];
    $columnGroups = ['mana_value', 'level'];
    // Short column labels: "Level 4" → "4", "Rank 4" → "R4", "Link 2" → "L2".
    $short = fn ($value) => preg_replace(['/^Level /', '/^Rank /', '/^Link /'], ['', 'R', 'L'], (string) $value);
    $colour = fn (string $group, $value) => $groups[$group]['colors'][(string) $value] ?? null;
@endphp

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
            @foreach ($stats->zones as $key => $zoneStats)
            <button type="button" @click="zone = '{{ $key }}'" :aria-pressed="zone === '{{ $key }}'"
                :class="zone === '{{ $key }}' ? 'bg-surface-2 font-bold text-ink' : 'font-medium text-muted hover:text-ink'"
                class="focus-ring min-h-10 flex-1 rounded-[9px] text-sm">{{ Str::before($zoneStats['label'], ' ') === 'Main' || count($stats->zones) > 2 ? Str::before($zoneStats['label'], ' ') : $zoneStats['label'] }}</button>
            @endforeach
        </div>

        @foreach ($stats->zones as $key => $zoneStats)
        <div x-show="zone === '{{ $key }}'" @if (! $loop->first) x-cloak @endif class="flex flex-col gap-5">
            @if ($zoneStats['count'] === 0)
            <p class="px-2 py-6 text-center text-sm text-muted">No cards in the {{ $zoneStats['label'] }}.</p>
            @else
            @foreach ($zoneStats['groups'] as $groupKey => $group)
            @php
                $values = $group['values'];
                $max = max($values ?: [1]);
            @endphp
            <div class="flex flex-col gap-2">
                <h3 class="font-mono text-xs uppercase tracking-[0.08em] text-muted">{{ $group['label'] }}</h3>

                @if (! $values)
                <p class="text-sm text-muted">None</p>
                @elseif ($loop->first)
                <div class="flex h-3.5 gap-0.5 overflow-hidden rounded-[5px]" aria-hidden="true">
                    @foreach ($values as $value => $count)
                    <span class="bg-accent" style="flex-grow: {{ $count }}; {{ $colour($groupKey, $value) ? 'background-color: '.$colour($groupKey, $value) : '' }}"></span>
                    @endforeach
                </div>
                <ul class="flex flex-wrap gap-x-3.5 gap-y-1.5 text-[13px] text-body">
                    @foreach ($values as $value => $count)
                    <li class="inline-flex items-center gap-1.5">
                        <span aria-hidden="true" class="h-2.5 w-2.5 rounded-[3px] bg-accent" @if ($colour($groupKey, $value)) style="background-color: {{ $colour($groupKey, $value) }}" @endif></span>
                        {{ $value }} <b class="font-mono text-ink">{{ $count }}</b>
                    </li>
                    @endforeach
                </ul>
                @elseif (in_array($groupKey, $columnGroups, true))
                <div class="flex h-[120px] items-end gap-1.5 border-b border-line">
                    @foreach ($values as $value => $count)
                    <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1" title="{{ $value }}: {{ $count }}">
                        <span class="font-mono text-[11px] font-bold text-ink">{{ $count }}</span>
                        <span class="block w-full rounded-t bg-accent" style="height: {{ round($count / $max * 100) }}%"></span>
                    </div>
                    @endforeach
                </div>
                <div class="flex gap-1.5" aria-hidden="true">
                    @foreach ($values as $value => $count)
                    <span class="min-w-0 flex-1 truncate text-center font-mono text-[11px] text-muted">{{ $short($value) }}</span>
                    @endforeach
                </div>
                <ul class="sr-only">
                    @foreach ($values as $value => $count)
                    <li>{{ $value }}: {{ $count }}</li>
                    @endforeach
                </ul>
                @else
                <ul class="flex flex-col gap-2">
                    @foreach ($values as $value => $count)
                    <li class="flex items-center gap-2.5">
                        <span class="w-20 truncate font-mono text-xs text-body">{{ $value }}</span>
                        <span aria-hidden="true" class="h-2 flex-1 overflow-hidden rounded-full bg-surface-2">
                            <span class="block h-full rounded-full bg-accent" style="width: {{ round($count / $max * 100) }}%; {{ $colour($groupKey, $value) ? 'background-color: '.$colour($groupKey, $value) : '' }}"></span>
                        </span>
                        <span class="w-6 text-right font-mono text-xs font-bold text-ink">{{ $count }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
            @endif

            <div class="flex justify-between gap-3 border-t border-line pt-3.5 text-[13px] text-muted">
                <span>{{ $zoneStats['label'] }} price</span>
                <span class="font-mono font-bold text-ink">{{ $euro($zoneStats['price']) }}</span>
            </div>
        </div>
        @endforeach

        <p class="-mt-2 text-xs text-muted">
            @if ($stats->missingPrices)
            {{ $stats->missingPrices }} {{ Str::plural('card', $stats->missingPrices) }} {{ $stats->missingPrices === 1 ? 'has' : 'have' }} no price ·
            @endif
            Cardmarket prices in euros
        </p>
    </div>
</aside>

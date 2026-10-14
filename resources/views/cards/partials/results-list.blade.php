{{-- List view: a compact table. The rows (tbody[data-results]) are what the next page appends. --}}
<div class="overflow-hidden rounded-2xl border border-line bg-surface">
    <table class="w-full text-left text-sm">
        <thead class="font-mono text-[11px] uppercase tracking-[0.08em] text-muted">
            <tr>
                <th scope="col" class="px-4 py-3 font-normal">Name</th>
                <th scope="col" class="px-4 py-3 font-normal">Type</th>
                <th scope="col" class="px-4 py-3 font-normal max-sm:hidden">Rarity</th>
                <th scope="col" class="px-4 py-3 font-normal max-sm:hidden">Set</th>
                <th scope="col" class="px-4 py-3 text-right font-normal">Price</th>
            </tr>
        </thead>
        <tbody data-results>
            @foreach ($cards as $card)
            <tr class="border-t border-line">
                <td class="px-4 py-1.5">
                    <a href="{{ $card->link() }}" class="focus-ring flex min-h-11 items-center gap-3 rounded-sm font-bold text-ink hover:text-accent-ink">
                        <img src="{{ $card->imageSmall() }}" alt="" loading="lazy" class="h-11 w-8 shrink-0 rounded-sm object-cover">
                        {{ $card->name() }}
                    </a>
                </td>
                <td class="px-4 py-1.5 text-body">{{ $card->typeLine() }}</td>
                <td class="px-4 py-1.5 max-sm:hidden"><span class="rarity-pill">{{ $card->rarityLabel() }}</span></td>
                <td class="px-4 py-1.5 text-body max-sm:hidden">{{ $card->setName() }}</td>
                <td class="px-4 py-1.5 text-right font-mono font-bold text-ink">{{ $card->formattedPrice() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

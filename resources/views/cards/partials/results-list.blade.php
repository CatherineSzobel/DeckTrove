{{-- List view: a compact table. The rows (tbody[data-results]) are what the next page appends. On phones only Name (with the type under it) and Price fit. --}}
<div class="overflow-hidden rounded-2xl border border-line bg-surface">
    <table class="w-full text-left text-sm">
        <thead class="font-mono text-[11px] uppercase tracking-[0.08em] text-muted">
            <tr>
                <th scope="col" class="px-3 py-3 font-normal sm:px-4">Name</th>
                <th scope="col" class="px-3 py-3 font-normal max-sm:hidden sm:px-4">Type</th>
                <th scope="col" class="px-3 py-3 font-normal max-sm:hidden sm:px-4">Rarity</th>
                <th scope="col" class="px-3 py-3 font-normal max-sm:hidden sm:px-4">Set</th>
                <th scope="col" class="px-3 py-3 text-right font-normal sm:px-4">Price</th>
            </tr>
        </thead>
        <tbody data-results>
            @foreach ($cards as $card)
            <tr class="border-t border-line">
                <td class="px-3 py-1.5 sm:px-4">
                    <a href="{{ $card->link() }}" class="focus-ring flex min-h-11 items-center gap-3 rounded-sm font-bold text-ink hover:text-accent-ink">
                        <img src="{{ $card->imageSmall() }}" alt="" loading="lazy" class="h-11 w-8 shrink-0 rounded-sm object-cover">
                        <span class="min-w-0">
                            {{ $card->name() }}
                            <span class="block text-xs font-normal text-muted sm:hidden">{{ $card->typeLine() }}</span>
                        </span>
                    </a>
                </td>
                <td class="px-3 py-1.5 text-body max-sm:hidden sm:px-4">{{ $card->typeLine() }}</td>
                <td class="px-3 py-1.5 max-sm:hidden sm:px-4"><span class="rarity-pill">{{ $card->rarityLabel() }}</span></td>
                <td class="px-3 py-1.5 text-body max-sm:hidden sm:px-4">{{ $card->setName() }}</td>
                <td class="px-3 py-1.5 text-right font-mono font-bold text-ink sm:px-4">{{ $card->formattedPrice() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

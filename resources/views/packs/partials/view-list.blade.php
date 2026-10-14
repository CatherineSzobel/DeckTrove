{{-- List view: a compact table of packs. The Type column only exists for series whose packs have a type. --}}
<div class="overflow-hidden rounded-2xl border border-line bg-surface">
    <table class="w-full text-left text-sm">
        <thead class="font-mono text-[11px] uppercase tracking-[0.08em] text-muted">
            <tr>
                <th scope="col" class="px-4 py-3 font-normal"><span class="sr-only">Cover</span></th>
                <th scope="col" class="px-4 py-3 font-normal">Name</th>
                <th scope="col" class="px-4 py-3 font-normal max-sm:hidden">Code</th>
                @if ($showType)
                <th scope="col" class="px-4 py-3 font-normal max-sm:hidden">Type</th>
                @endif
                <th scope="col" class="px-4 py-3 font-normal">Released</th>
                <th scope="col" class="px-4 py-3 text-right font-normal">Cards</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($packs as $pack)
            <tr class="border-t border-line">
                <td class="px-4 py-1.5"><x-pack.cover :pack="$pack" size="xs" /></td>
                <td class="px-4 py-1.5">
                    <a href="{{ $pack->link() }}" class="focus-ring flex min-h-11 items-center rounded-sm font-bold text-ink hover:text-accent-ink">{{ $pack->name() }}</a>
                </td>
                <td class="px-4 py-1.5 font-mono text-body max-sm:hidden">{{ $pack->codeLabel() }}</td>
                @if ($showType)
                <td class="px-4 py-1.5 text-body max-sm:hidden">{{ $pack->typeLabel() }}</td>
                @endif
                <td class="px-4 py-1.5 text-body">{{ $pack->releaseLabel() }}</td>
                <td class="px-4 py-1.5 text-right font-mono font-bold text-ink">{{ $pack->cardCount() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

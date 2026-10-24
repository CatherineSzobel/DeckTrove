{{-- One face of a double-faced card on the card page ($face: App\Cards\CardFace, $index, $front). Without JS every face
    shows, the back ones with their own image; with JS (cardFaces) only the face on the big image shows. --}}
<section data-face="{{ $index }}" aria-label="{{ $face->name() }}" class="flex flex-col gap-5"
    @unless ($front) data-collapsed="true" @endunless :data-collapsed="index !== {{ $index }}">
    @unless ($front)
    <div class="no-js-only w-full max-w-[240px]">
        <img src="{{ $face->image() }}" alt="{{ $face->name() }}" loading="lazy" class="aspect-[59/86] w-full rounded-[14px] object-cover">
    </div>
    @endunless
    <div class="flex flex-col gap-2">
        @if ($face->typeLine())
        <p class="eyebrow">{{ $face->typeLine() }}</p>
        @endif
        <h2 class="heading-display text-[32px] text-ink">{{ $face->name() }}</h2>
    </div>
    @if ($face->detailStats())
    <dl class="flex flex-wrap gap-3">
        @foreach ($face->detailStats() as $stat)
        <x-stat-box :label="$stat['label']" :value="$stat['value']" />
        @endforeach
    </dl>
    @endif
    @if ($face->text())
    <div class="whitespace-pre-line rounded-2xl border border-line bg-surface px-6 py-5 text-[17px] leading-[1.7] text-body">{{ $face->text() }}</div>
    @endif
</section>

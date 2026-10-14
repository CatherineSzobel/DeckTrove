{{-- Screenshots and selling points next to the log in / create account card (App\View\Components\Auth\Showcase). Hidden on small screens. --}}
<section aria-label="About DeckTrove" class="hidden min-w-0 flex-[1_1_440px] flex-col gap-8 lg:flex">
    <x-showcase-shots />

    <ol class="flex flex-col gap-3.5">
        @foreach ($points as $point)
        <li class="flex items-center gap-3.5 text-base text-body">
            <span class="font-mono text-[13px] text-accent-ink">{{ $point['number'] }}</span>
            {{ $point['text'] }}
        </li>
        @endforeach
    </ol>
</section>

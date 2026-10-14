{{--
    The card database's results: filter chips, the count, the cards in the current view and the next-page link.
    Also the AJAX response that resources/js/card-database.js swaps in or takes the next cards from.
--}}
<div class="flex flex-col gap-6">
    <div class="flex min-h-11 flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($query->chips() as $chip)
            <span class="inline-flex items-center gap-1 rounded-full bg-surface-2 py-1 pl-3 pr-1 text-[13px] font-medium text-ink">
                {{ $chip['label'] }}
                <a href="{{ $chip['removeUrl'] }}" data-apply-link aria-label="Remove {{ $chip['filterLabel'] }} filter"
                    class="focus-ring -my-2.5 flex h-11 w-11 items-center justify-center rounded-full text-muted hover:text-ink">
                    <x-heroicon-o-x-mark class="h-4 w-4" aria-hidden="true" />
                </a>
            </span>
            @endforeach
            @if ($query->isFiltering())
            <a href="{{ $query->clearUrl() }}" data-apply-link class="focus-ring rounded-sm px-1 py-2 text-[13px] font-bold text-accent-ink">Clear all</a>
            @endif
        </div>
        <p data-result-count aria-live="polite" class="font-mono text-[13px] text-muted">@include('cards.partials.result-count')</p>
    </div>

    @if ($cards->isEmpty())
    <x-empty-state title="No cards found">
        Try another search or clear the filters.
        <x-slot:actions>@if ($query->isFiltering())<a href="{{ $query->clearUrl() }}" data-apply-link class="btn-secondary">Clear all</a>@endif</x-slot:actions>
    </x-empty-state>
    @else
    @include("cards.partials.results-{$query->view()}")
    @endif

    @if ($query->nextPageUrl($cards))
    <a href="{{ $query->nextPageUrl($cards) }}" data-load-more class="btn-secondary self-center">Load more cards</a>
    @endif
</div>

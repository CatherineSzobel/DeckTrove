<div class="max-w-7xl mx-auto">
    @if ($cards->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-2xl bg-white py-24 text-center shadow-sm">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">No cards found</h2>
        <p class="text-gray-500 mb-6 max-w-md">Try adjusting your search or clearing filters to see more results.</p>
    </div>
    @else
    @include("cards.partials.cards-{$currentView}")

    @if ($cards->hasPages())
    <div class="mt-16 flex justify-center">
        <div class="rounded-2xl bg-white shadow-sm px-10 py-8">
            {{ $cards->withPath(route('cards.index', $series))->links() }}
        </div>
    </div>
    @endif
    @endif
</div>

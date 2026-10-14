@if ($cards->isNotEmpty())
Showing {{ $cards->lastItem() }} of {{ number_format($cards->total()) }} cards
@else
No cards found
@endif

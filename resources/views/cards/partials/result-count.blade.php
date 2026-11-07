@if ($cards->isNotEmpty())
Showing {{ number_format($cards->firstItem()) }}–{{ number_format($cards->lastItem()) }} of {{ number_format($cards->total()) }} cards
@else
No cards found
@endif

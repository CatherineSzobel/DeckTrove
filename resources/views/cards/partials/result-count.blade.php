@if ($cards->total())
Showing {{ $cards->firstItem() }}–{{ $cards->lastItem() }} of {{ number_format($cards->total()) }} cards
@else
No cards found
@endif

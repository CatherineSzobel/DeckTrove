@props(['packs', 'listId'])

{{-- The sets a card was printed in (CardViewModel::printPacks()): the first four, the rest behind "Show more". --}}

<div class="mb-4">
    @if ($packs->isNotEmpty())
    <ul class="list-inside list-disc text-blue-500 font-bold">
        @foreach ($packs->take(4) as $pack)
        <li><a href="{{ $pack['url'] }}" class="hover:text-blue-800">{{ $pack['set_name'] }}</a></li>
        @endforeach
    </ul>

    @if ($packs->count() > 4)
    <ul id="{{ $listId }}" class="hidden list-inside list-disc text-blue-500 font-bold mt-2">
        @foreach ($packs->slice(4) as $pack)
        <li><a href="{{ $pack['url'] }}" class="hover:text-blue-800">{{ $pack['set_name'] }}</a></li>
        @endforeach
    </ul>

    <button type="button" class="text-blue-600 underline mt-1" data-toggle-target="{{ $listId }}" aria-expanded="false" aria-controls="{{ $listId }}">
        Show more
    </button>
    @endif
    @else
    <p class="text-slate-500">No prints available</p>
    @endif
</div>

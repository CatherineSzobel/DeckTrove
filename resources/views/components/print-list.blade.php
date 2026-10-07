@props(['sets', 'series'])

@php
    // One entry per pack: a print's set code looks like "LOB-EN001", the pack code is "LOB".
    $packs = collect($sets)
        ->map(fn ($set) => [
            'pack_code' => explode('-', $set['set_code'] ?? '')[0],
            'set_name' => $set['set_name'] ?? 'Unknown',
        ])
        ->filter(fn ($set) => $set['pack_code'] !== '')
        ->unique('pack_code')
        ->values();

    $listId = 'more-prints-'.($attributes->get('id') ?? 'default');
@endphp

<div class="mb-4">
    @if ($packs->isNotEmpty())
    <ul class="list-inside list-disc text-blue-500 font-bold">
        @foreach ($packs->take(4) as $pack)
        <li><a href="{{ route('packs.show', [$series, $pack['pack_code']]) }}" class="hover:text-blue-800">{{ $pack['set_name'] }}</a></li>
        @endforeach
    </ul>

    @if ($packs->count() > 4)
    <ul id="{{ $listId }}" class="hidden list-inside list-disc text-blue-500 font-bold mt-2">
        @foreach ($packs->slice(4) as $pack)
        <li><a href="{{ route('packs.show', [$series, $pack['pack_code']]) }}" class="hover:text-blue-800">{{ $pack['set_name'] }}</a></li>
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

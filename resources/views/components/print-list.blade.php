@props(['sets', 'series'])

@php
    // One entry per set. A print code looks like "LOB-EN001"; the pack code is the part before the dash.
    // Several products can share a pack code, so the set name's slug is part of the link.
    $packs = collect($sets)
        ->map(fn ($set) => [
            'pack_code' => explode('-', $set['set_code'] ?? '')[0],
            'set_name' => $set['set_name'] ?? 'Unknown',
        ])
        ->filter(fn ($set) => $set['pack_code'] !== '')
        ->unique('set_name')
        ->map(fn ($set) => $set + ['url' => route('packs.show', [$series, $set['pack_code'], Str::slug($set['set_name'])])])
        ->values();

    $listId = 'more-prints-'.($attributes->get('id') ?? 'default');
@endphp

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

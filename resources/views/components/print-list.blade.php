@props(['sets', 'tcgGame' => 'yugioh'])

@php
// Normalize + dedupe print sets
$uniqueSets = collect($sets)
->map(function($set) {
$raw = $set['set_code'] ?? '';
$pack = explode('-', $raw)[0] ?? $raw;

return [
'pack_code' => $pack,
'set_name' => $set['set_name'] ?? 'Unknown',
];
})
->unique('pack_code')
->values(); // reindex

$hasMore = $uniqueSets->count() > 4;

// Build a stable unique id for the hidden list
$id = 'more-prints-' . ($attributes->get('id') ?? 'default');

// Base URL by TCG
$baseUrl = match(strtolower($tcgGame)) {
'magic' => '/magic/pack/',
default => '/yugioh/pack/',
};
@endphp

<div class="mb-4">

    @if($uniqueSets->isNotEmpty())
    <ul class="list-inside list-disc text-blue-400 font-bold">
        @foreach($uniqueSets->take(4) as $set)
        <li>
            <a href="{{ url($baseUrl . urlencode($set['pack_code'])) }}"
                class="hover:text-blue-800">
                {{ $set['set_name'] }}
            </a>
        </li>
        @endforeach
    </ul>

    {{-- Hidden items --}}
    @if($hasMore)
    <ul id="{{ $id }}" class="hidden list-inside list-disc text-blue-400 font-bold mt-2">
        @foreach($uniqueSets->slice(4) as $set)
        <li>
            <a href="{{ url($baseUrl . urlencode($set['pack_code'])) }}"
                class="hover:text-blue-800">
                {{ $set['set_name'] }}
            </a>
        </li>
        @endforeach
    </ul>

    <button
        type="button"
        class="text-blue-600 underline mt-1"
        data-toggle-target="{{ $id }}"
        aria-expanded="false">
        Show More
    </button>
    @endif

    @else
    <p class="text-slate-200">No prints available</p>
    @endif
</div>
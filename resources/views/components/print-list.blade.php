{{-- Printings on the card page; App\View\Components\PrintList splits them into the first four and the rest. --}}

@if ($first->isEmpty())
<p class="text-muted">No printings listed.</p>
@else
<div x-data="printList({{ $more->count() }})" class="flex flex-col gap-1">
    <ul class="border-t border-line">
        @foreach ($first as $pack)
        <x-print-list-row :pack="$pack" />
        @endforeach
    </ul>

    @if ($more->isNotEmpty())
    <ul id="more-printings" data-collapsed="true" :data-collapsed="!open">
        @foreach ($more as $pack)
        <x-print-list-row :pack="$pack" />
        @endforeach
    </ul>
    <button type="button" @click="toggle()" :aria-expanded="open" aria-controls="more-printings" x-text="label"
        class="js-only focus-ring inline-flex min-h-11 items-center self-start rounded-sm text-sm font-bold text-accent-ink">Show {{ $more->count() }} more</button>
    @endif
</div>
@endif

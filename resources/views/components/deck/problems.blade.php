@props(['title', 'messages' => []])

{{-- Cards that break the deck's format, listed with a hint on how to fix it (the slot). --}}
<section role="alert" aria-label="Format problems"
    {{ $attributes->class('flex items-start gap-4 rounded-2xl border border-danger-line bg-danger-bg px-6 py-5') }}>
    <x-heroicon-o-exclamation-triangle class="mt-0.5 h-6 w-6 shrink-0 text-danger" aria-hidden="true" />
    <div class="flex min-w-0 flex-1 flex-col gap-2">
        <h2 class="heading-display text-2xl text-ink">{{ $title }}</h2>
        <ul class="list-disc space-y-1 pl-5 text-[15px] text-body">
            @foreach ($messages as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
        @if ($slot->isNotEmpty())
        <p class="text-sm text-muted">{{ $slot }}</p>
        @endif
    </div>
</section>

{{-- Switches between light and dark mode; behaviour lives in resources/js/layout.js. --}}
<button type="button" data-theme-toggle aria-label="Switch colour theme" title="Switch colour theme"
    {{ $attributes->merge(['class' => 'focus-ring inline-flex h-11 w-11 items-center justify-center rounded-[10px] border border-line text-body transition-colors hover:text-ink']) }}>
    <x-heroicon-o-moon class="h-5 w-5 [.dark_&]:hidden" />
    <x-heroicon-o-sun class="hidden h-5 w-5 [.dark_&]:block" />
</button>

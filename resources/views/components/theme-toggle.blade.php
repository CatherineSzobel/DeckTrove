{{-- Switches between light and dark mode; behaviour lives in resources/js/layout.js. --}}
<button type="button" data-theme-toggle aria-label="Toggle dark mode" title="Toggle dark mode"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-md p-2 text-slate-300 hover:bg-white/5 hover:text-white']) }}>
    <x-heroicon-o-moon class="h-5 w-5 [.dark_&]:hidden" />
    <x-heroicon-o-sun class="hidden h-5 w-5 [.dark_&]:block" />
</button>

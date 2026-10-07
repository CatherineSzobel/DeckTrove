<button {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700', 'type' => 'button']) }}>
    {{ $slot }}
    <x-heroicon-m-chevron-down class="ml-1 h-4 w-4" />
</button>

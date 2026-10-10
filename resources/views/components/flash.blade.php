{{-- Session notices (success / warning / error), styled like the redesign's notices. Dismissable with Alpine. --}}
@php
    $notices = [
        'success' => ['role' => 'status', 'icon' => 'heroicon-o-check-circle', 'box' => 'border-ok-line bg-ok-bg', 'tint' => 'text-ok'],
        'warning' => ['role' => 'alert', 'icon' => 'heroicon-o-exclamation-triangle', 'box' => 'border-warn-line bg-warn-bg', 'tint' => 'text-accent-ink'],
        'error' => ['role' => 'alert', 'icon' => 'heroicon-o-x-circle', 'box' => 'border-danger-line bg-danger-bg', 'tint' => 'text-danger'],
    ];
@endphp

@foreach ($notices as $key => $notice)
@if (session($key))
<div role="{{ $notice['role'] }}" data-flash="{{ $key }}" x-data="{ shown: true }" x-show="shown"
    class="mb-4 flex items-center gap-3 rounded-xl border px-4 py-3 text-[15px] text-ink {{ $notice['box'] }}">
    <x-dynamic-component :component="$notice['icon']" class="h-5 w-5 shrink-0 {{ $notice['tint'] }}" />
    <span class="flex-1">{{ session($key) }}</span>
    <button type="button" @click="shown = false" aria-label="Dismiss"
        class="focus-ring flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-muted hover:text-ink">
        <x-heroicon-o-x-mark class="h-5 w-5" />
    </button>
</div>
@endif
@endforeach

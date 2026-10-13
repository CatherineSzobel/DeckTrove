{{-- Session notices from App\View\Components\Flash, styled by the .notice class in app.css. Dismissable with Alpine. --}}
@foreach ($notices as $notice)
<div role="{{ $notice['role'] }}" data-flash="{{ $notice['type'] }}" x-data="{ shown: true }" x-show="shown" class="notice">
    <x-dynamic-component :component="$notice['icon']" class="notice-icon h-5 w-5 shrink-0" />
    <span class="flex-1">{{ $notice['message'] }}</span>
    <button type="button" @click="shown = false" aria-label="Dismiss"
        class="focus-ring flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-muted hover:text-ink">
        <x-heroicon-o-x-mark class="h-5 w-5" />
    </button>
</div>
@endforeach

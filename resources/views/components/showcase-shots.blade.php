{{-- Four screenshots that take turns on a soft accent glow, and a toggle that pauses them
(App\View\Components\ShowcaseShots; the loop and the toggle are pure CSS in app.css). The log in / create account
showcase and the home page hero; pages clip the swipe-off with overflow-x-clip. --}}
<div {{ $attributes->class('showcase flex flex-col gap-4') }}>
    <div class="relative aspect-[5/3.6]">
        <div class="showcase-glow" aria-hidden="true"></div>
        @foreach ($shots as $shot)
        <img src="{{ Vite::asset($shot['path']) }}" alt="{{ $shot['alt'] }}" data-shot="{{ $shot['number'] }}" class="showcase-shot">
        @endforeach
    </div>

    <label class="showcase-pause">
        <input type="checkbox" class="sr-only">
        <x-heroicon-o-pause class="h-4 w-4 shrink-0" aria-hidden="true" />
        Pause screenshots
    </label>
</div>

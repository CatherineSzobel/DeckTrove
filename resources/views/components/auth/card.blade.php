@props(['eyebrow', 'title'])

{{-- The form card of the log in, create account and password pages: eyebrow, heading, intro, the form, a footer line. --}}
<section aria-labelledby="auth-title" {{ $attributes->class('flex w-full min-w-0 max-w-[460px] flex-[1_1_380px] flex-col gap-7 rounded-3xl border border-line bg-surface p-6 sm:p-10') }}>
    <div class="flex flex-col gap-2.5">
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1 id="auth-title" class="heading-display text-[52px] text-ink">{{ $title }}</h1>
        @isset($intro)
        {{ $intro }}
        @endisset
    </div>

    {{ $slot }}

    @isset($footer)
    <p class="text-[15px] text-body">{{ $footer }}</p>
    @endisset
</section>

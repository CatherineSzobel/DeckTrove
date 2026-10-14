@props(['user', 'label' => 'Profile'])

{{-- A profile card (account page and public profile): the banner, the avatar over its edge, then the slot. --}}
<section aria-label="{{ $label }}" {{ $attributes->class('min-w-0 overflow-hidden rounded-3xl border border-line bg-surface') }}>
    <div class="profile-banner" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="flex flex-col gap-4 px-7 pb-7">
        <x-avatar :user="$user" class="relative -mt-[52px] h-[104px] w-[104px] border-4 border-surface font-display text-[44px] font-extrabold" />
        {{ $slot }}
    </div>
</section>

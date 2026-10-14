@props(['pack'])

{{-- One printing in x-print-list: the set name and its pack code, linked to the pack page. --}}
<li class="border-b border-line">
    <a href="{{ $pack['url'] }}" class="focus-ring flex min-h-12 items-center justify-between gap-3 font-medium text-ink hover:text-accent-ink">
        <span>{{ $pack['set_name'] }}</span>
        <span class="font-mono text-[13px] text-accent-ink">{{ $pack['code'] }}</span>
    </a>
</li>

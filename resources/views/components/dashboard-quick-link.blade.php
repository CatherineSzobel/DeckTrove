@props(['links' => []])

{{-- $links: [['href' => ..., 'title' => ...], ...], from DashboardService::quickLinks(). --}}
<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    @foreach ($links as $link)
    <a href="{{ $link['href'] }}"
        class="group mb-4 flex items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-800 px-6 py-4 font-semibold text-white shadow-md transition-all hover:scale-105 hover:border-amber-500 hover:shadow-lg">
        {{ $link['title'] }}
    </a>
    @endforeach
</div>

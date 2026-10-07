@props(['links' => []])

{{-- $links: [['href' => ..., 'title' => ..., 'class' => ...], ...] --}}
<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    @foreach ($links as $link)
    <a href="{{ $link['href'] }}"
        class="{{ $link['class'] ?? 'bg-slate-800 hover:bg-slate-700' }} group flex items-center justify-center gap-2 px-6 py-4 rounded-xl text-white font-semibold shadow-md transition-all transform hover:scale-105 hover:shadow-lg mb-4">
        {{ $link['title'] }}
    </a>
    @endforeach
</div>

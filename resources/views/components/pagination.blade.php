{{-- Numbered pages (App\View\Components\Pagination): 44px squares, the current page in accent. --}}
<nav aria-label="Pagination" class="flex flex-wrap justify-center gap-2 font-mono text-sm">
    @unless ($paginator->onFirstPage())
    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page" class="page-link">
        <x-heroicon-o-chevron-left class="h-4 w-4" aria-hidden="true" />
    </a>
    @endunless

    @foreach ($links as $link)
    @if ($link['current'])
    <span aria-current="page" class="page-link">{{ $link['page'] }}</span>
    @elseif ($link['url'])
    <a href="{{ $link['url'] }}" class="page-link">{{ $link['page'] }}</a>
    @else
    <span class="page-link" data-gap aria-hidden="true">…</span>
    @endif
    @endforeach

    @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page" class="page-link">
        <x-heroicon-o-chevron-right class="h-4 w-4" aria-hidden="true" />
    </a>
    @endif
</nav>

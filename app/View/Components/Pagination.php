<?php

namespace App\View\Components;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\UrlWindow;
use Illuminate\View\Component;

/**
 * Numbered pages (resources/views/components/pagination.blade.php): previous / next arrows and one flat list of
 * links, with a gap (no url) wherever Laravel's UrlWindow skips pages. `.page-link` in app.css styles them.
 */
class Pagination extends Component
{
    private const GAP = ['page' => null, 'url' => null, 'current' => false];

    /** @var list<array{page: int|null, url: string|null, current: bool}> */
    public array $links;

    public function __construct(public LengthAwarePaginator $paginator)
    {
        $window = UrlWindow::make($paginator);

        $this->links = collect([$window['first'], $window['slider'], $window['last']])
            ->filter()
            ->map(fn (array $urls) => collect($urls)
                ->map(fn (string $url, int $page) => ['page' => $page, 'url' => $url, 'current' => $page === $paginator->currentPage()])
                ->values()
                ->all())
            ->reduce(fn (array $links, array $group) => $links === [] ? $group : [...$links, self::GAP, ...$group], []);
    }

    public function shouldRender(): bool
    {
        return $this->paginator->hasPages();
    }

    public function render(): View
    {
        return view('components.pagination');
    }
}

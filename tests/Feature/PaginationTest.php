<?php

use App\View\Components\Pagination;
use Illuminate\Pagination\LengthAwarePaginator;

function pageLinks(int $pages, int $current): array
{
    $paginator = (new LengthAwarePaginator([], $pages * 10, 10, $current))->onEachSide(1);

    return collect((new Pagination($paginator))->links)
        ->map(fn (array $link) => $link['url'] ? ($link['current'] ? "[{$link['page']}]" : $link['page']) : '…')
        ->all();
}

test('a few pages are all listed, with the current one marked', function () {
    expect(pageLinks(3, 2))->toBe([1, '[2]', 3]);
});

test('many pages keep the ends and the pages around the current one, with gaps between', function () {
    expect(pageLinks(20, 10))->toBe([1, 2, '…', 9, '[10]', 11, '…', 19, 20])
        ->and(pageLinks(20, 1))->toBe(['[1]', 2, 3, 4, 5, 6, '…', 19, 20]);
});

test('one page renders nothing', function () {
    expect((new Pagination(new LengthAwarePaginator([], 5, 10, 1)))->shouldRender())->toBeFalse();
});

<?php

use App\Cards\CardQuery;
use App\Cards\FilterChoices;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

function cardQuery(array $params = [], string $series = 'yugioh', array $options = []): CardQuery
{
    return CardQuery::fromRequest(Request::create("/$series/cards", 'GET', $params), $series, FilterChoices::for($series, $options));
}

test('the view falls back to images', function () {
    expect(cardQuery()->view())->toBe('images')
        ->and(cardQuery(['view' => 'list'])->view())->toBe('list')
        ->and(cardQuery(['view' => 'inner'])->view())->toBe('images')
        ->and(cardQuery(['view' => ['list']])->view())->toBe('images');
});

test('the search is trimmed, cut to 100 characters and null when empty', function () {
    expect(cardQuery(['search' => '  dark  '])->search())->toBe('dark')
        ->and(cardQuery(['search' => '   '])->search())->toBeNull()
        ->and(cardQuery(['search' => ['dark']])->search())->toBeNull()
        ->and(mb_strlen(cardQuery(['search' => str_repeat('é', 150)])->search()))->toBe(100);
});

test('only the series filters with a text value are kept, in config order', function () {
    $query = cardQuery(['race' => 'Dragon', 'attribute' => '', 'archetype' => ['x'], 'color' => 'R', 'type' => ' Effect Monster ']);

    expect($query->filters())->toBe(['type' => 'Effect Monster', 'race' => 'Dragon']);
});

test('the provider gets the filters, search, view and page', function () {
    expect(cardQuery(['race' => 'Dragon', 'search' => 'blue', 'page' => '3'])->params())
        ->toBe(['race' => 'Dragon', 'search' => 'blue', 'view' => 'images', 'page' => 3])
        ->and(cardQuery(['page' => '-2'])->params())->toBe(['view' => 'images', 'page' => 1])
        ->and(cardQuery(['page' => ['2']])->params())->toBe(['view' => 'images', 'page' => 1]);
});

test('links keep the search and filters, and drop the page and the default view', function () {
    $query = cardQuery(['race' => 'Dragon', 'search' => 'blue', 'page' => '2']);

    expect($query->url())->toBe(route('cards.index', ['yugioh', 'search' => 'blue', 'race' => 'Dragon']))
        ->and($query->url(['race' => null]))->toBe(route('cards.index', ['yugioh', 'search' => 'blue']))
        ->and($query->url(['view' => 'list']))->toBe(route('cards.index', ['yugioh', 'view' => 'list', 'search' => 'blue', 'race' => 'Dragon']))
        ->and($query->url(['view' => 'images']))->toBe(route('cards.index', ['yugioh', 'search' => 'blue', 'race' => 'Dragon']))
        ->and($query->pageUrl(3))->toBe(route('cards.index', ['yugioh', 'search' => 'blue', 'race' => 'Dragon', 'page' => 3]));
});

test('the next page link exists only when there are more pages', function () {
    $query = cardQuery(['search' => 'blue']);

    expect($query->nextPageUrl(new LengthAwarePaginator([], 100, 24, 2)))
        ->toBe(route('cards.index', ['yugioh', 'search' => 'blue', 'page' => 3]))
        ->and($query->nextPageUrl(new LengthAwarePaginator([], 30, 24, 2)))->toBeNull();
});

test('view links come in the order Full, Images, List and mark the current one', function () {
    $links = cardQuery(['view' => 'list', 'search' => 'blue'])->viewLinks();

    expect(array_column($links, 'key'))->toBe(['full', 'images', 'list'])
        ->and(array_column($links, 'label'))->toBe(['Full', 'Images', 'List'])
        ->and(array_column($links, 'current'))->toBe([false, false, true])
        ->and($links[0]['href'])->toBe(route('cards.index', ['yugioh', 'view' => 'full', 'search' => 'blue']))
        ->and($links[1]['href'])->toBe(route('cards.index', ['yugioh', 'search' => 'blue']));
});

test('each filter is a chip with nice labels and a link without it', function () {
    $query = cardQuery(
        ['color' => 'R', 'set_name' => 'gone', 'search' => 'bolt'],
        'magic',
        ['color' => ['R'], 'set_name' => ['dmu' => 'Dominaria United']],
    );

    expect($query->chips())->toBe([
        ['key' => 'color', 'filterLabel' => 'Color', 'label' => 'Color: Red', 'removeUrl' => route('cards.index', ['magic', 'search' => 'bolt', 'set_name' => 'gone'])],
        ['key' => 'set_name', 'filterLabel' => 'Set', 'label' => 'Set: gone', 'removeUrl' => route('cards.index', ['magic', 'search' => 'bolt', 'color' => 'R'])],
    ]);
});

test('the filter count, the filtering state and the clear link', function () {
    $query = cardQuery(['race' => 'Dragon', 'type' => 'Effect Monster', 'search' => 'blue', 'view' => 'list']);

    expect($query->activeCount())->toBe(2)
        ->and($query->isFiltering())->toBeTrue()
        ->and($query->clearUrl())->toBe(route('cards.index', ['yugioh', 'view' => 'list']))
        ->and(cardQuery(['search' => 'blue'])->activeCount())->toBe(0)
        ->and(cardQuery(['search' => 'blue'])->isFiltering())->toBeTrue()
        ->and(cardQuery()->isFiltering())->toBeFalse();
});

test('the form knows the selected options and the hidden view field', function () {
    $query = cardQuery(['race' => 'Dragon', 'view' => 'full']);

    expect($query->isSelected('race', 'Dragon'))->toBeTrue()
        ->and($query->isSelected('race', 'Fiend'))->toBeFalse()
        ->and($query->isSelected('type', 'Effect Monster'))->toBeFalse()
        ->and($query->hiddenFields())->toBe(['view' => 'full'])
        ->and(cardQuery()->hiddenFields())->toBe([]);
});

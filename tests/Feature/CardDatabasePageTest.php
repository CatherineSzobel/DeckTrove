<?php

use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'race' => 'Spellcaster'])->create();
    YugiohCard::factory()->fromCard(['id' => 89631139, 'name' => 'Blue-Eyes White Dragon', 'race' => 'Dragon'])->create();
});

test('the page has the eyebrow, the heading and one game switch', function () {
    $html = $this->get(route('cards.index', 'yugioh'))->assertOk()->getContent();

    expect($html)->toContain('<p class="eyebrow">Card database</p>')
        ->toContain('Yu-Gi-Oh! cards</h1>')
        ->and(substr_count($html, 'data-game-switch'))->toBe(1);
});

test('full is the default view, with a box per card linking to the card page in the same tab', function () {
    $html = $this->get(route('cards.index', 'yugioh'))->getContent();

    expect($html)->toMatch('/data-label="Full"\s+aria-current="page"/')
        ->toContain('<article')
        ->toContain('href="'.route('cards.show', ['yugioh', 46986414]).'" class="focus-ring')
        ->toContain('Normal Monster — Spellcaster')
        ->not->toContain('card-tile')
        ->not->toMatch('/<a href="[^"]*\/card\/[^"]*"[^>]*target=/')
        ->not->toContain('<table');
});

test('the images view shows card tiles that link to the card page in the same tab', function () {
    $html = $this->get(route('cards.index', ['yugioh', 'view' => 'images']))->getContent();

    expect($html)->toMatch('/data-label="Images"\s+aria-current="page"/')
        ->toContain('href="'.route('cards.show', ['yugioh', 46986414]).'" class="card-tile"')
        ->toContain('<input type="hidden" name="view" value="images">')
        ->not->toMatch('/class="card-tile"[^>]*target=/');
});

test('the view links keep the search and filters', function () {
    $this->get(route('cards.index', ['yugioh', 'search' => 'dark', 'race' => 'Spellcaster']))
        ->assertSee('href="'.e(route('cards.index', ['yugioh', 'view' => 'list', 'search' => 'dark', 'race' => 'Spellcaster'])).'"', false);
});

test('the list view is a table and the search keeps the view', function () {
    $html = $this->get(route('cards.index', ['yugioh', 'view' => 'list']))->getContent();

    expect($html)->toContain('<table')
        ->toContain('<tbody data-results>')
        ->toContain('<input type="hidden" name="view" value="list">')
        ->toMatch('/data-label="List"\s+aria-current="page"/');
});

test('the list and full views show the rarity as a pill', function (string $view) {
    $this->get(route('cards.index', ['yugioh', 'view' => $view]))->assertOk()
        ->assertSee('<span class="rarity-pill">Ultra Rare</span>', false);
})->with(['list', 'full']);

test('the search field keeps the search, escaped', function () {
    $this->get(route('cards.index', ['yugioh', 'search' => '<b>dark</b>']))->assertOk()
        ->assertSee('value="&lt;b&gt;dark&lt;/b&gt;"', false)
        ->assertDontSee('value="<b>', false);
});

test('a filter is preselected and shows as a chip with a remove link, plus clear all', function () {
    $html = $this->get(route('cards.index', ['yugioh', 'race' => 'Dragon']))->getContent();

    expect($html)->toContain('<option value="Dragon" selected')
        ->toContain('Race: Dragon')
        ->toContain('href="'.route('cards.index', 'yugioh').'" data-apply-link aria-label="Remove Race filter"')
        ->toContain('>Clear all</a>');
});

test('without a search or filters there are no chips and no clear all', function () {
    $this->get(route('cards.index', 'yugioh'))
        ->assertDontSee('data-apply-link', false)
        ->assertDontSee('Clear all');
});

test('the count says how many cards are shown so far', function () {
    $this->get(route('cards.index', 'yugioh'))->assertSee('Showing 2 of 2 cards');
});

test('a load more link goes to the next page when there are more cards', function () {
    YugiohCard::factory()->count(47)->create();
    // Full is the default view: 24 per page.

    $html = $this->get(route('cards.index', 'yugioh'))->getContent();

    expect($html)->toContain('href="'.e(route('cards.index', ['yugioh', 'page' => 2])).'" data-load-more')
        ->toContain('Load more cards')
        ->toContain('Showing 24 of 49 cards');

    $this->get(route('cards.index', ['yugioh', 'page' => 2]))
        ->assertSee('Showing 48 of 49 cards')
        ->assertSee('data-load-more', false);

    $this->get(route('cards.index', ['yugioh', 'page' => 3]))
        ->assertSee('Showing 49 of 49 cards')
        ->assertDontSee('data-load-more', false);
});

test('one page of cards has no load more link and no numbered pages', function () {
    $this->get(route('cards.index', 'yugioh'))
        ->assertDontSee('data-load-more', false)
        ->assertDontSee('role="navigation"', false);
});

test('no results show the empty state with clear all when searching', function () {
    $this->get(route('cards.index', ['yugioh', 'search' => 'zzzz']))->assertOk()
        ->assertSee('No cards found')
        ->assertSee('Try another search or clear the filters.')
        ->assertSee('href="'.route('cards.index', 'yugioh').'" data-apply-link class="btn-secondary"', false);
});

test('a page past the last one shows the empty state, not a broken count', function () {
    $this->get(route('cards.index', ['yugioh', 'page' => 999]))->assertOk()
        ->assertSee('No cards found')
        ->assertDontSee('Showing  of', false);
});

test('without JS the filters can be applied with a button', function () {
    $this->get(route('cards.index', 'yugioh'))
        ->assertSee('<div id="filters"', false)
        ->assertSee('Apply filters');
});

test('the old card database pieces are gone', function () {
    $this->get(route('cards.index', 'yugioh'))
        ->assertDontSee('filterDetails', false)
        ->assertDontSee('viewtype-selector', false)
        ->assertDontSee('cards-loader', false);
});

test('the page loads results in place with the card database component', function () {
    $html = $this->get(route('cards.index', 'yugioh'))->getContent();

    expect($html)->toContain('x-data="cardDatabase"')
        ->toContain('x-ref="form" @submit.prevent="apply()"')
        ->toContain('@change="apply()"')
        ->toContain('x-ref="region" @click="onRegionClick($event)" :aria-busy="loading"')
        ->toContain('Loading more cards…');
});

test('the filter panel starts collapsed without filters and open with one', function () {
    expect($this->get(route('cards.index', 'yugioh'))->getContent())
        ->toMatch('/id="filters" x-ref="filters"\s+data-collapsed="true"/')
        ->not->toMatch('/data-active\s+class="filter-toggle js-only"/');

    expect($this->get(route('cards.index', ['yugioh', 'race' => 'Dragon']))->getContent())
        ->not->toContain('data-collapsed="true"')
        ->toMatch('/data-active\s+class="filter-toggle js-only"/')
        ->toMatch('/<span class="filter-count" x-show="activeCount" x-text="activeCount"\s*>1<\/span>/');
});

test('the filters button controls the panel and the apply button is only for no JS', function () {
    $this->get(route('cards.index', 'yugioh'))
        ->assertSee('aria-controls="filters" :aria-expanded="showFilters"', false)
        ->assertSee('class="btn-secondary no-js-only">Apply filters', false);
});

test('the page and the results partial vary on how they are requested', function () {
    $this->get(route('cards.index', 'yugioh'))->assertHeader('Vary', 'X-Requested-With');
    $this->get(route('cards.index', 'yugioh'), ['X-Requested-With' => 'XMLHttpRequest'])->assertHeader('Vary', 'X-Requested-With');
});

test('the search panel is wrapped in a bar that sticks to the top when JS runs', function () {
    $this->get(route('cards.index', 'yugioh'))
        ->assertSee('<div class="search-bar">', false);

    expect(file_get_contents(resource_path('css/app.css')))->toContain('.js .search-bar');
});

test('the view switch does not move when Alpine starts', function () {
    $none = $this->get(route('cards.index', 'yugioh'))->getContent();
    $some = $this->get(route('cards.index', ['yugioh', 'race' => 'Dragon']))->getContent();

    expect($none)->toMatch('/<button type="button" @click="showFilters[\s\S]*?class="filter-toggle js-only"/')
        ->not->toMatch('/<button[^>]*x-cloak[^>]*filter-toggle/')
        ->toMatch('/<span class="filter-count" x-show="activeCount" x-text="activeCount"\s+style="display: none"\s*>0<\/span>/')
        ->toContain('data-view="full" data-label="Full"')
        ->toContain('data-label="Images"')
        ->toContain('data-label="List"')
        ->and($some)->not->toContain('style="display: none">1</span>');

    expect(file_get_contents(resource_path('css/app.css')))->toContain('html:not(.js) .js-only');
});

test('image tiles show the card preview on hover and focus, with the description', function () {
    YugiohCard::factory()->fromCard(['id' => 12345, 'name' => 'Preview Card', 'desc' => 'Unique preview text.'])->create();

    $html = $this->get(route('cards.index', ['yugioh', 'view' => 'images']))->getContent();

    expect($html)->toContain('@mouseenter="showPreview(card, $el)" @mouseleave="hidePreview()"')
        ->toContain('@focus="showPreview(card, $el)" @blur="hidePreview()"')
        ->toContain('Unique preview text.')
        ->toMatch('/class="card-tile"[\s\S]*?x-data="\{ card:|x-data="\{ card:[\s\S]*?class="card-tile"/')
        ->and(substr_count($html, 'data-card-preview'))->toBe(1)
        ->and($html)->toContain('@scroll.window.passive="hideOnScroll()"');
});

test('the full and list views have no card preview handlers on their cards', function (string $view) {
    expect($this->get(route('cards.index', ['yugioh', 'view' => $view]))->getContent())
        ->not->toContain('showPreview(card, $el)');
})->with(['full', 'list']);

test('the AJAX images response has the preview handlers too', function () {
    $html = $this->get(route('cards.index', ['yugioh', 'view' => 'images']), ['X-Requested-With' => 'XMLHttpRequest'])->getContent();

    expect($html)->toContain('@mouseenter="showPreview(card, $el)"')
        ->not->toContain('data-card-preview');
});

test('the view nav is a ref, so the component finds it from any event', function () {
    $this->get(route('cards.index', 'yugioh'))->assertSee('<nav aria-label="Card view" x-ref="views"', false);
});

test('on phones the list view drops the type column and shows the type under the name', function () {
    $html = $this->get(route('cards.index', ['yugioh', 'view' => 'list']))->getContent();

    expect($html)->toContain('<th scope="col" class="px-3 py-3 font-normal max-sm:hidden sm:px-4">Type</th>')
        ->toContain('<span class="block text-xs font-normal text-muted sm:hidden">Normal Monster — Spellcaster</span>');
});

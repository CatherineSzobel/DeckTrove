<?php

use Illuminate\Support\Facades\Blade;

function counterHtml(string $attributes): string
{
    return Blade::render('<x-collection.counter game="yugioh" card="46986414" name="Dark Magician" '.$attributes.' />');
}

test('the counter is two plain forms around the count, labelled with the card', function () {
    $html = counterHtml(':count="2"');

    expect($html)
        ->toContain('action="'.route('collection.remove').'"')
        ->toContain('action="'.route('collection.add').'"')
        ->toContain('aria-label="Add a copy of Dark Magician"')
        ->toContain('aria-label="Remove a copy of Dark Magician"')
        ->toContain('name="list" value="owned"')
        ->toContain('name="printing" value=""')
        ->toContain('x-data="collectionCounter"')
        ->toContain('data-count="2"')
        ->toMatch('/aria-live="polite"[^>]*>2</')
        ->not->toContain('btn-secondary');
});

test('minus is off at zero, on otherwise', function () {
    // Only the remove button can be disabled; `:disabled="…"` (Alpine) is not the server-rendered attribute.
    expect(counterHtml(':count="0"'))->toContain('<button type="submit" disabled')
        ->and(counterHtml(':count="2"'))->not->toContain('<button type="submit" disabled');
});

test('when every copy is in a printing, minus is off and says where to remove it', function () {
    expect(counterHtml(':count="2" :removable="false"'))
        ->toContain('<button type="submit" disabled')
        ->toContain('aria-describedby="counter-hint-yugioh-46986414-owned-"')
        ->toContain('Remove it from a printing on the card page.');
});

test('a printing or wishlist counter posts its list and printing', function () {
    expect(counterHtml(':count="1" printing="LOB-EN005"'))->toContain('name="printing" value="LOB-EN005"')
        ->and(counterHtml(':count="1" list="wanted"'))->toContain('name="list" value="wanted"');
});

test('the card page row uses the larger count', function () {
    expect(counterHtml(':count="1" size="row"'))->toContain('text-lg')
        ->and(counterHtml(':count="1"'))->not->toContain('text-lg');
});

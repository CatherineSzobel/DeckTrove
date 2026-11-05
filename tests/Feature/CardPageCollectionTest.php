<?php

use App\Collection\CollectionService;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(fn () => YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_sets' => [
    ['set_name' => 'Legend of Blue Eyes White Dragon', 'set_code' => 'LOB-EN005', 'set_rarity' => 'Ultra Rare'],
    ['set_name' => 'Starter Deck: Yugi', 'set_code' => 'SDY-006', 'set_rarity' => 'Common'],
]])->create());

function cardPageWithPrintings(?User $user): string
{
    $request = $user ? test()->actingAs($user) : test();

    return $request->get(route('cards.show', ['yugioh', '46986414']))->assertOk()->getContent();
}

test('a player sees one slim collection row with a counter and the heart', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', '', 1);
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', 'LOB-EN005', 2);

    $html = cardPageWithPrintings($user);

    expect($html)->toContain('id="in-collection"')
        ->toContain('In your collection')
        ->toContain('2 by printing · 1 any printing')
        ->toContain('data-count="3"')
        ->toContain('action="'.route('collection.want').'"')
        ->toContain('name="want" value="1"')
        ->toContain('Want it')
        ->not->toContain('Track by printing');
});

test('the heart says Wanted and posts want=0 when the card is on the wishlist', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'wanted');

    expect(cardPageWithPrintings($user))->toContain('Wanted')->toContain('aria-pressed="true"')->toContain('name="want" value="0"');
});

test('the Printings list has a counter per printing, linking each to its pack', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', 'LOB-EN005', 2);

    $html = cardPageWithPrintings($user);

    expect($html)->toContain('name="printing" value="LOB-EN005"')
        ->toContain('name="printing" value="SDY-006"')
        ->toContain(route('packs.show', ['yugioh', 'LOB', 'legend-of-blue-eyes-white-dragon']))
        ->toContain('aria-label="Add a copy of Dark Magician (Legend of Blue Eyes White Dragon (LOB-EN005))"');
});

test('a stored printing the source no longer offers keeps its row', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', 'LOB-EN005');
    $user->collectionItems()->update(['printing' => 'OLD-EN001']);

    expect(cardPageWithPrintings($user))->toContain('name="printing" value="OLD-EN001"');
});

test('a printing the card doesn’t have can’t be added', function () {
    $user = User::factory()->create();

    expect(fn () => app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', 'anything'))
        ->toThrow(ValidationException::class, 'That card has no such printing.')
        ->and($user->collectionItems()->count())->toBe(0);
});

test('removing a copy that isn’t there says so', function () {
    $user = User::factory()->create();

    expect(fn () => app(CollectionService::class)->remove($user, 'yugioh', '46986414', 'wanted'))
        ->toThrow(ValidationException::class, 'There is no copy of that card to remove.');
});

test('with every copy in printings, the row’s minus is off and points to the printings', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', 'LOB-EN005');

    expect(cardPageWithPrintings($user))->toContain('Remove it from a printing on the card page.');
});

test('guests see today’s page: no row, no heart, no counters', function () {
    $html = cardPageWithPrintings(null);

    expect($html)->not->toContain('in-collection')->not->toContain('collectionCounter')->not->toContain(route('collection.want'))
        ->toContain('Printings');
});

test('a Magic card page still works for a player when Scryfall can’t list the printings', function () {
    $id = '11111111-1111-4111-8111-111111111111';
    Http::fake([
        // Only the printings search fails; "More from this set" (also a search) still answers.
        'api.scryfall.com/cards/search*' => fn ($request) => str_contains(urldecode($request->url()), 'oracleid:')
            ? Http::response('', 503)
            : Http::response(['data' => [], 'total_cards' => 0, 'has_more' => false]),
        'api.scryfall.com/cards/*' => Http::response(['id' => $id, 'oracle_id' => '22222222-2222-4222-8222-222222222222', 'name' => 'Llanowar Elves', 'set' => 'dom', 'set_name' => 'Dominaria', 'rarity' => 'common']),
    ]);

    $html = $this->actingAs(User::factory()->create())->get(route('cards.show', ['magic', $id]))->assertOk()->getContent();

    // The row's own counter posts an empty printing; no per-printing counter may appear, and the list says it's empty.
    expect($html)->toContain('id="in-collection"')->toContain('No printings listed.')->not->toMatch('/name="printing" value="[^"]+"/');
});

test('without JS, the row and the Printings counters go back to their own section', function () {
    $html = cardPageWithPrintings(User::factory()->create());

    expect(substr_count($html, 'name="anchor" value="in-collection"'))->toBe(2) // the row's − and +
        ->and(substr_count($html, 'name="anchor" value="printings"'))->toBe(4); // − and + on both printings
});

test('the row, its counter and the heart carry the card so they can follow the Printings counters', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', 'LOB-EN005', 2);

    expect(cardPageWithPrintings($user))->toContain('x-data="collectionRow"')
        ->toContain('data-owned="2" data-note="2 by printing"')
        ->toContain('data-card="46986414" data-list="owned" data-printing=""')
        ->toContain('data-wanted="0" data-game="yugioh" data-card="46986414"')
        ->toContain('@collection-changed.window="sync($event.detail)"');
});

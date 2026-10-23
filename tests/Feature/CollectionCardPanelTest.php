<?php

use App\Collection\CollectionService;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_sets' => [
        ['set_code' => 'LOB-EN005', 'set_name' => 'Legend of Blue Eyes White Dragon'],
        ['set_code' => 'SDY-EN006', 'set_name' => 'Starter Deck: Yugi'],
    ]])->create();
});

test('guests see no collection panel and no + buttons', function () {
    $this->get(route('cards.show', ['yugioh', '46986414']))->assertOk()->assertDontSee('In your collection');
    $this->get(route('cards.index', 'yugioh'))->assertDontSee(route('collection.add'));
});

test('the card page shows the owned count and the heart', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', amount: 2);
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'wanted');

    $this->actingAs($user)->get(route('cards.show', ['yugioh', '46986414']))
        ->assertSee('In your collection')
        ->assertSee('data-count="2"', false)
        ->assertSee('Wanted')
        ->assertSee('aria-label="Add a copy of Dark Magician"', false);
});

test('the Printings list counts each printing', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', printing: 'LOB-EN005');

    $this->actingAs($user)->get(route('cards.show', ['yugioh', '46986414']))
        ->assertDontSee('Track by printing')
        ->assertSeeInOrder(['Legend of Blue Eyes White Dragon', 'LOB-EN005', 'data-count="1"', 'Starter Deck: Yugi', 'SDY-EN006', 'data-count="0"'], false)
        ->assertSee('1 by printing');
});

test('card database tiles get a counter for logged-in players, outside the card link', function () {
    foreach (['images', 'full', 'list'] as $view) {
        $html = $this->actingAs(User::factory()->create())->get(route('cards.index', ['yugioh', 'view' => $view]))->getContent();

        expect($html)->toContain('aria-label="Add a copy of Dark Magician"')
            ->and($html)->not->toMatch('/<a [^>]*>(?:(?!<\/a>).)*<form/s');
    }
});

test('the card database’s AJAX results carry the counter too', function () {
    $this->actingAs(User::factory()->create())->get(route('cards.index', 'yugioh'), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertSee('aria-label="Add a copy of Dark Magician"', false);
});

test('when every copy is in a printing the row keeps its + and loses only the −', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', printing: 'LOB-EN005');

    $html = $this->actingAs($user)->get(route('cards.show', ['yugioh', '46986414']))->getContent();

    expect($html)->toContain('aria-label="Add a copy of Dark Magician"')
        ->and($html)->toMatch('/<button type="submit" disabled[^>]*aria-label="Remove a copy of Dark Magician"/')
        ->and($html)->toContain('Remove it from a printing on the card page.');
});

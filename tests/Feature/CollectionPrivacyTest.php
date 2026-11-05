<?php

use App\Collection\CollectionService;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
});

test('Edit profile saves both toggles', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), ['username' => $user->username, 'collection_public' => '1'])->assertRedirect();

    expect($user->fresh())->collection_public->toBeTrue()->wishlist_public->toBeFalse();
});

test('private lists are a 404 for other players and hidden from the overview', function (string $route, string $section) {
    $owner = User::factory()->create();
    app(CollectionService::class)->add($owner, 'yugioh', '46986414');

    $this->actingAs(User::factory()->create())->get(route($route, $owner))->assertNotFound();
    // The logged-in nav has its own "Collection" link, so check the section and jump link, not the word.
    $this->get(route('users.show', $owner))->assertDontSee('id="'.$section.'"', false)->assertDontSee('href="#'.$section.'"', false);
})->with([
    'collection' => ['users.collection', 'collection'],
    'wishlist' => ['users.wishlist', 'wishlist'],
]);

test('a public collection shows on the overview and its own page, read-only', function () {
    $owner = User::factory()->create(['collection_public' => true]);
    app(CollectionService::class)->add($owner, 'yugioh', '46986414', amount: 3);

    $this->get(route('users.show', $owner))->assertSee('Collection')->assertSee('Dark Magician')->assertSee('×3')
        ->assertSee('aria-label="Dark Magician, ×3"', false);
    $this->get(route('users.collection', $owner))->assertOk()->assertSee('Dark Magician')->assertSee('×3')
        ->assertSee('Collection · '.$owner->username)
        ->assertDontSee(route('collection.add'));
});

test('the owner sees their private lists with the Only you badge', function () {
    $owner = User::factory()->create();
    app(CollectionService::class)->add($owner, 'yugioh', '46986414', 'wanted');

    $this->actingAs($owner)->get(route('users.show', $owner))->assertSee('Wishlist')->assertSee('Only you');
    $this->actingAs($owner)->get(route('users.wishlist', $owner))->assertOk();
});

test('a public list page has no steppers and keeps the series links', function () {
    $owner = User::factory()->create(['wishlist_public' => true]);
    app(CollectionService::class)->add($owner, 'yugioh', '46986414', 'wanted');

    $this->get(route('users.wishlist', $owner))->assertOk()
        ->assertSee('Dark Magician')
        ->assertSee(route('users.wishlist', [$owner, 'series' => 'magic']), false)
        ->assertDontSee('Remove one');
});

test('the gates let the owner in always and others while the toggle is on', function () {
    $owner = User::factory()->create(['collection_public' => true]);

    expect(Gate::forUser(User::factory()->create())->allows('viewCollection', $owner))->toBeTrue()
        ->and(Gate::forUser(User::factory()->create())->allows('viewWishlist', $owner))->toBeFalse()
        ->and(Gate::forUser($owner)->allows('viewWishlist', $owner))->toBeTrue()
        ->and(Gate::forUser(null)->allows('viewCollection', $owner))->toBeTrue();
});

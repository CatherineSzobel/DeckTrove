<?php

use App\Decks\PublicDeckQuery;
use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

function likedByQuery(User $liker, array $params = []): PublicDeckQuery
{
    return PublicDeckQuery::fromRequest(Request::create('/', 'GET', $params))->likedBy($liker);
}

test('likedBy lists only public decks the user liked', function () {
    $liker = User::factory()->create();
    $liked = Deck::factory()->public()->create(['name' => 'Liked Public']);
    $madePrivate = Deck::factory()->public()->create(['name' => 'Made Private']);
    Deck::factory()->public()->create(['name' => 'Not Liked']);
    $othersLike = Deck::factory()->public()->create(['name' => 'Liked By Others']);
    DeckLike::factory()->for($liker)->for($liked)->create();
    DeckLike::factory()->for($liker)->for($madePrivate)->create();
    DeckLike::factory()->for($othersLike)->create();
    $madePrivate->update(['is_public' => false]);

    $names = collect(likedByQuery($liker)->paginate(12)->items())->pluck('name')->all();

    expect($names)->toBe(['Liked Public']);
});

test('likedBy keeps the sort and series but ignores search, format and the liked filter', function () {
    $liker = User::factory()->create();

    $filters = likedByQuery($liker, ['q' => 'x', 'game' => 'magic', 'format' => 'casual', 'sort' => 'liked', 'liked' => 1])->filters();

    expect($filters)->toBe(['q' => null, 'sort' => 'liked', 'game' => 'magic', 'format' => null, 'liked' => false]);
});

test('likedBy links stay on the liked page', function () {
    $liker = User::factory()->create(['username' => 'fan']);

    expect(likedByQuery($liker, ['game' => 'magic'])->url(['sort' => 'liked']))
        ->toBe(route('users.liked', [$liker, 'sort' => 'liked', 'game' => 'magic']));
});

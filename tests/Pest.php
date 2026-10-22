<?php

use App\Models\Card;
use App\Models\Deck;
use App\Models\DeckCard;
use App\Models\User;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

// A public Yu-Gi-Oh! deck that takes comments, for the deck comment tests.
function commentableDeck(array $attributes = []): Deck
{
    return Deck::factory()->public()->create(['game' => 'yugioh'] + $attributes);
}

// Copies of a card on a user's collection or wishlist, for the collection tests.
function ownedCount(User $user, string $key = '46986414'): int
{
    return (int) $user->collectionItems()->owned()->where('card_key', $key)->sum('quantity');
}

// A private deck with [zone => [external id => copies]], storing the cards rows as deck saving does.
function collectionDeck(string $game, array $zones): Deck
{
    $deck = Deck::factory()->create(['game' => $game]);

    foreach ($zones as $zone => $cards) {
        foreach ($cards as $externalId => $count) {
            $card = Card::firstOrCreate(['game' => $game, 'external_id' => (string) $externalId], ['name' => "Card $externalId"]);
            DeckCard::create(['deck_id' => $deck->id, 'card_id' => $card->id, 'zone' => $zone, 'count' => $count]);
        }
    }

    return $deck;
}

function wantedCount(User $user, string $key = '46986414'): int
{
    return (int) $user->collectionItems()->wanted()->where('card_key', $key)->sum('quantity');
}

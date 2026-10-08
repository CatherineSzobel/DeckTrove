<?php

use App\Models\YugiohCard;
use App\Models\YugiohSet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Writes a small YGOPRODeck-style dump and returns the file path.
 */
function dumpFile(string $name, array $contents): string
{
    $path = storage_path("framework/testing/$name.json");
    @mkdir(dirname($path), 0777, true);
    file_put_contents($path, json_encode($contents));

    return $path;
}

function importFixture(): void
{
    $sets = dumpFile('sets', [
        ['set_name' => 'Dark Legion Starter Deck', 'set_code' => 'YS15', 'num_of_cards' => 29, 'tcg_date' => '2015-05-29', 'set_image' => 'https://images.ygoprodeck.com/images/sets/YS15.jpg'],
        ['set_name' => 'Saber Force Starter Deck', 'set_code' => 'YS15', 'num_of_cards' => 28, 'tcg_date' => '2015-05-29'],
        ['set_name' => 'Legend of Blue Eyes White Dragon', 'set_code' => 'LOB', 'num_of_cards' => 126, 'tcg_date' => '2002-03-08'],
    ]);

    $cards = dumpFile('cards', ['data' => [
        YugiohCard::factory()->card(['id' => 1, 'name' => 'Dark Card', 'card_sets' => [
            ['set_name' => 'Dark Legion Starter Deck', 'set_code' => 'YS15-ENL01', 'set_rarity' => 'Common'],
        ]]),
        YugiohCard::factory()->card(['id' => 2, 'name' => 'Saber Card', 'card_sets' => [
            ['set_name' => 'Saber Force Starter Deck', 'set_code' => 'YS15-ENY01', 'set_rarity' => 'Common'],
            ['set_name' => 'Legend of Blue Eyes White Dragon', 'set_code' => 'LOB-EN001', 'set_rarity' => 'Ultra Rare'],
            ['set_name' => 'Legend of Blue Eyes White Dragon', 'set_code' => 'LOB-EN001', 'set_rarity' => 'Secret Rare'],
        ]]),
        YugiohCard::factory()->card(['id' => 3, 'name' => 'Promo Card', 'card_sets' => [
            ['set_name' => 'Some promo missing from the set list', 'set_code' => 'PRM-EN001', 'set_rarity' => 'Rare'],
        ]]),
    ]]);

    test()->artisan('yugioh:import', ['--file' => $cards, '--sets-file' => $sets])->assertSuccessful();
}

test('the import stores sets and which cards were printed in them', function () {
    importFixture();

    expect(YugiohSet::count())->toBe(3);
    expect(YugiohSet::where('name', 'Legend of Blue Eyes White Dragon')->sole()->printings()->count())->toBe(2);

    // Running it again replaces rather than duplicates.
    importFixture();
    expect(YugiohSet::count())->toBe(3);
    expect(YugiohSet::where('name', 'Legend of Blue Eyes White Dragon')->sole()->printings()->count())->toBe(2);
});

test('products sharing a set code are separate pack pages', function () {
    importFixture();

    $this->get('/yugioh/pack/YS15/dark-legion-starter-deck')
        ->assertOk()->assertSee('Dark Legion Starter Deck')->assertSee('Dark Card')->assertDontSee('Saber Card');

    $this->get('/yugioh/pack/YS15/saber-force-starter-deck')
        ->assertOk()->assertSee('Saber Card')->assertDontSee('Dark Card');

    $this->get('/yugioh/pack/YS15/not-a-real-deck')->assertNotFound();
});

test('a pack page shows the rarity in that set, not the first printing', function () {
    importFixture();

    $this->get('/yugioh/pack/LOB')->assertOk()->assertSee('Ultra Rare / Secret Rare')->assertDontSee('>Common<', false);
});

test('pack lists link each product to its own page', function () {
    importFixture();

    $this->get(route('packs.index', ['yugioh', 'search' => 'starter']))
        ->assertOk()
        ->assertSee('/yugioh/pack/YS15/dark-legion-starter-deck', false)
        ->assertSee('/yugioh/pack/YS15/saber-force-starter-deck', false)
        ->assertDontSee('Legend of Blue Eyes');
});

test('card pages list each printing set separately', function () {
    importFixture();

    $this->get(route('cards.show', ['yugioh', '2']))
        ->assertOk()
        ->assertSee('/yugioh/pack/YS15/saber-force-starter-deck', false)
        ->assertSee('/yugioh/pack/LOB/legend-of-blue-eyes-white-dragon', false);
});

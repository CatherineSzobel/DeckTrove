<?php

use App\ViewModels\CardViewModel;

/** A raw card of this series with this rarity (Yu-Gi-Oh!: its first printing's; Magic: Scryfall's `rarity`). */
function holoCard(string $series, ?string $rarity): CardViewModel
{
    $raw = match ($series) {
        'yugioh' => ['id' => 1, 'name' => 'Card', 'card_sets' => $rarity === null ? [] : [['set_name' => 'Set', 'set_code' => 'SET-EN001', 'set_rarity' => $rarity]]],
        'magic' => array_filter(['id' => 'card', 'name' => 'Card', 'rarity' => $rarity]),
    };

    return new CardViewModel($raw, config("series.$series"));
}

test('yugioh foil rarities are holo', function (string $rarity, bool $holo) {
    expect(holoCard('yugioh', $rarity)->isHolo())->toBe($holo);
})->with([
    ['Super Rare', true],
    ['Ultra Rare', true],
    ['Starlight Rare', true],
    ['Quarter Century Secret Rare', true],
    ['Duel Terminal Ultra Parallel Rare', true],
    ['Super Parallel Rare', true],
    ["Collector's Rare", true],
    ['PLatinum Secret Rare', true],
    ['Common', false],
    ['Rare', false],
    ['Short Print', false],
    ['Super Short Print', false],
    ['New', false],
]);

test('magic rares and up are holo', function (string $rarity, bool $holo) {
    expect(holoCard('magic', $rarity)->isHolo())->toBe($holo);
})->with([
    ['rare', true],
    ['mythic', true],
    ['special', true],
    ['bonus', true],
    ['common', false],
    ['uncommon', false],
]);

test('a card without a rarity, or a series without holo rarities, is not holo', function () {
    expect(holoCard('yugioh', null)->isHolo())->toBeFalse()
        ->and(holoCard('magic', null)->isHolo())->toBeFalse();

    config(['series.yugioh' => collect(config('series.yugioh'))->except('holo_rarities')->all()]);

    expect(holoCard('yugioh', 'Secret Rare')->isHolo())->toBeFalse();
});

test('holo rarities are lists of words', function () {
    foreach (['yugioh', 'magic'] as $series) {
        $words = config("series.$series.holo_rarities");

        expect($words)->toBeArray()->not->toBeEmpty()
            ->and(array_is_list($words))->toBeTrue()
            ->and(collect($words)->every(fn ($word) => is_string($word) && $word !== ''))->toBeTrue();
    }
});

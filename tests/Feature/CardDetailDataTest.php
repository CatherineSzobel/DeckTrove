<?php

use App\ViewModels\CardViewModel;

function detailCard(string $series, array $card): CardViewModel
{
    return new CardViewModel($card, config("series.$series"));
}

function euros(string $amount): string
{
    return config('app.currency.symbol').$amount;
}

test('the type line joins a yugioh card type and race', function () {
    expect(detailCard('yugioh', ['type' => 'Effect Monster', 'race' => 'Spellcaster'])->typeLine())->toBe('Effect Monster — Spellcaster')
        ->and(detailCard('yugioh', ['type' => 'Spell Card'])->typeLine())->toBe('Spell Card')
        ->and(detailCard('yugioh', [])->typeLine())->toBe('');
});

test('the type line of a magic card is its type line, without the mana cost', function () {
    expect(detailCard('magic', ['mana_cost' => '{G}', 'type_line' => 'Creature — Elf Druid'])->typeLine())->toBe('Creature — Elf Druid');
});

test('a yugioh monster shows its ATK, DEF and price', function () {
    expect(detailCard('yugioh', ['type' => 'Normal Monster', 'atk' => 2500, 'def' => 2100, 'card_prices' => [['cardmarket_price' => '0.10']]])->detailStats())
        ->toBe([
            ['label' => 'ATK', 'value' => '2500'],
            ['label' => 'DEF', 'value' => '2100'],
            ['label' => 'Price', 'value' => euros('0.10')],
        ]);
});

test('a link monster shows LINK instead of DEF', function () {
    expect(detailCard('yugioh', ['type' => 'Link Monster', 'atk' => 1500, 'linkval' => 2])->detailStats())
        ->toBe([
            ['label' => 'ATK', 'value' => '1500'],
            ['label' => 'LINK', 'value' => '2'],
            ['label' => 'Price', 'value' => 'N/A'],
        ]);
});

test('a spell only shows its price', function () {
    expect(detailCard('yugioh', ['type' => 'Spell Card'])->detailStats())->toBe([['label' => 'Price', 'value' => 'N/A']]);
});

test('a magic creature shows its mana cost, power, toughness and price', function () {
    expect(detailCard('magic', ['mana_cost' => '{G}', 'power' => '1', 'toughness' => '1', 'prices' => ['eur' => '0.20']])->detailStats())
        ->toBe([
            ['label' => 'Mana cost', 'value' => '{G}'],
            ['label' => 'Power', 'value' => '1'],
            ['label' => 'Toughness', 'value' => '1'],
            ['label' => 'Price', 'value' => euros('0.20')],
        ]);
});

test('only a card with more than one face image is double-faced', function () {
    $face = fn (string $name) => ['image_uris' => ['normal' => "https://cards.scryfall.io/normal/$name.jpg"]];

    expect(detailCard('magic', ['card_faces' => [$face('front'), $face('back')]])->hasFaces())->toBeTrue()
        ->and(detailCard('magic', ['card_faces' => [$face('front'), ['mana_cost' => '']]])->hasFaces())->toBeFalse()
        ->and(detailCard('magic', [])->hasFaces())->toBeFalse()
        ->and(detailCard('yugioh', ['name' => 'Dark Magician'])->hasFaces())->toBeFalse();
});

test('a magic land has no mana cost box', function () {
    expect(detailCard('magic', ['mana_cost' => '', 'type_line' => 'Basic Land — Forest'])->detailStats())
        ->toBe([['label' => 'Price', 'value' => 'N/A']]);
});

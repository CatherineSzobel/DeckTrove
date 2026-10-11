<?php

use App\Cards\MagicCardMapper;
use App\Cards\YugiohCardMapper;
use App\ViewModels\CardViewModel;

test('yugioh cards are grouped by category, level and attribute', function (array $card, array $facets) {
    expect(app(YugiohCardMapper::class)->statFacets($card))->toBe($facets);
})->with([
    'normal monster' => [['type' => 'Normal Monster', 'level' => 7, 'attribute' => 'DARK'], ['category' => 'Monster', 'level' => 'Level 7', 'attribute' => 'DARK']],
    'xyz monster' => [['type' => 'XYZ Monster', 'level' => 4, 'attribute' => 'LIGHT'], ['category' => 'Monster', 'level' => 'Rank 4', 'attribute' => 'LIGHT']],
    'link monster' => [['type' => 'Link Monster', 'linkval' => 2, 'attribute' => 'EARTH'], ['category' => 'Monster', 'level' => 'Link 2', 'attribute' => 'EARTH']],
    'spell' => [['type' => 'Spell Card', 'race' => 'Quick-Play'], ['category' => 'Spell']],
    'trap' => [['type' => 'Trap Card', 'race' => 'Counter'], ['category' => 'Trap']],
    'skill' => [['type' => 'Skill Card'], []],
    'no card data' => [['id' => 1, 'name' => 'Gone'], []],
]);

test('magic cards are grouped by type, mana value and color', function (array $card, array $facets) {
    expect(app(MagicCardMapper::class)->statFacets($card))->toBe($facets);
})->with([
    'creature' => [['type_line' => 'Creature — Elf Druid', 'cmc' => 1.0, 'colors' => ['G']], ['type' => 'Creature', 'mana_value' => '1', 'color' => 'Green']],
    'artifact creature counts as creature' => [['type_line' => 'Artifact Creature — Golem', 'cmc' => 4.0, 'colors' => []], ['type' => 'Creature', 'mana_value' => '4', 'color' => 'Colorless']],
    'mana value zero' => [['type_line' => 'Artifact Creature — Thopter', 'cmc' => 0.0, 'colors' => []], ['type' => 'Creature', 'mana_value' => '0', 'color' => 'Colorless']],
    'multicolor instant' => [['type_line' => 'Instant', 'cmc' => 2.0, 'colors' => ['U', 'R']], ['type' => 'Instant', 'mana_value' => '2', 'color' => 'Multicolor']],
    'seven or more' => [['type_line' => 'Sorcery', 'cmc' => 9.0, 'colors' => ['R']], ['type' => 'Sorcery', 'mana_value' => '7+', 'color' => 'Red']],
    'basic land' => [['type_line' => 'Basic Land — Forest', 'cmc' => 0.0, 'colors' => []], ['type' => 'Land']],
    'artifact land counts as land' => [['type_line' => 'Artifact Land', 'cmc' => 0.0, 'colors' => []], ['type' => 'Land']],
    'land creature counts as land' => [['type_line' => 'Land Creature — Forest Dryad', 'cmc' => 0.0, 'colors' => ['G']], ['type' => 'Land']],
    'double-faced card' => [
        ['type_line' => 'Creature — Human Werewolf // Creature — Werewolf', 'cmc' => 2.0, 'card_faces' => [['type_line' => 'Creature — Human Werewolf', 'colors' => ['G']], ['type_line' => 'Creature — Werewolf', 'colors' => ['G']]]],
        ['type' => 'Creature', 'mana_value' => '2', 'color' => 'Green'],
    ],
    'spell with a land back face' => [
        ['type_line' => 'Sorcery // Land', 'cmc' => 3.0, 'card_faces' => [['type_line' => 'Sorcery', 'colors' => ['B']], ['type_line' => 'Land', 'colors' => []]]],
        ['type' => 'Sorcery', 'mana_value' => '3', 'color' => 'Black'],
    ],
    'no card data' => [['id' => 'x', 'name' => 'Gone'], []],
]);

test('every group a mapper returns is configured for its series', function (string $series, array $card) {
    $facets = app(config("series.$series.mapper"))->statFacets($card);

    expect($facets)->not->toBeEmpty();
    expect(array_keys($facets))->each->toBeIn(array_keys(config("series.$series.deck.stats")));
})->with([
    'yugioh' => ['yugioh', ['type' => 'Effect Monster', 'level' => 4, 'attribute' => 'DARK']],
    'magic' => ['magic', ['type_line' => 'Creature — Elf', 'cmc' => 1.0, 'colors' => ['G']]],
]);

test('the view model passes the stats groups through', function () {
    $card = new CardViewModel(['id' => 1, 'name' => 'A', 'type' => 'Trap Card'], config('series.yugioh'));

    expect($card->statFacets())->toBe(['category' => 'Trap']);
});

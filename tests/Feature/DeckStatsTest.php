<?php

use App\Decks\DeckStats;
use App\Decks\DeckStatsResult;
use App\Models\Card;
use App\Models\Deck;
use App\Models\YugiohCard;
use App\ViewModels\CardViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Copies of one card as buildSections() returns them: one view model per copy.
 *
 * @return list<CardViewModel>
 */
function statsCards(string $series, array $card, int $copies = 1): array
{
    return array_fill(0, $copies, new CardViewModel($card, config("series.$series")));
}

test('yugioh stats count each zone by category, level and attribute', function () {
    $sections = [
        'main' => collect([
            ...statsCards('yugioh', ['id' => 1, 'type' => 'Normal Monster', 'level' => 7, 'attribute' => 'DARK'], 3),
            ...statsCards('yugioh', ['id' => 2, 'type' => 'Effect Monster', 'level' => 4, 'attribute' => 'LIGHT'], 2),
            ...statsCards('yugioh', ['id' => 3, 'type' => 'Spell Card'], 1),
        ]),
        'extra' => collect(statsCards('yugioh', ['id' => 4, 'type' => 'XYZ Monster', 'level' => 4, 'attribute' => 'DARK'], 2)),
    ];

    $stats = app(DeckStats::class)->fromSections($sections, config('series.yugioh'));
    $main = $stats->zone('main');

    expect($main['label'])->toBe('Main Deck');
    expect($main['count'])->toBe(6);
    expect($main['groups']['category'])->toBe(['label' => 'Card types', 'values' => ['Monster' => 5, 'Spell' => 1]]);
    expect($main['groups']['level']['values'])->toBe(['Level 4' => 2, 'Level 7' => 3]);
    expect($main['groups']['attribute']['values'])->toBe(['DARK' => 3, 'LIGHT' => 2]);
    expect($stats->zone('extra')['groups']['level']['values'])->toBe(['Rank 4' => 2]);
    expect($stats->groupCount('main', 'category', 'Monster'))->toBe(5);
    expect($stats->groupCount('main', 'category', 'Trap'))->toBe(0);
});

test('zones without cards are listed with zeros', function () {
    $stats = app(DeckStats::class)->fromSections(['main' => collect()], config('series.yugioh'));

    expect(array_keys($stats->zones))->toBe(['main', 'extra', 'side']);
    expect($stats->zone('side'))->toBe([
        'label' => 'Side Deck',
        'count' => 0,
        'price' => 0.0,
        'groups' => [
            'category' => ['label' => 'Card types', 'values' => []],
            'level' => ['label' => 'Level / Rank', 'values' => []],
            'attribute' => ['label' => 'Attribute', 'values' => []],
        ],
    ]);
    expect($stats->price)->toBe(0.0);
    expect($stats->missingPrices)->toBe(0);
    expect($stats->zone('commander'))->toBeNull();
});

test('values follow the configured order, numbers included', function () {
    $sections = ['main' => collect([
        ...statsCards('magic', ['id' => 'a', 'type_line' => 'Sorcery', 'cmc' => 8.0, 'colors' => ['R']]),
        ...statsCards('magic', ['id' => 'b', 'type_line' => 'Instant', 'cmc' => 3.0, 'colors' => ['R']]),
        ...statsCards('magic', ['id' => 'c', 'type_line' => 'Basic Land — Mountain', 'cmc' => 0.0, 'colors' => []], 2),
        ...statsCards('magic', ['id' => 'd', 'type_line' => 'Creature — Goblin', 'cmc' => 1.0, 'colors' => ['R']]),
        ...statsCards('magic', ['id' => 'e', 'type_line' => 'Artifact Creature — Thopter', 'cmc' => 0.0, 'colors' => []]),
    ])];

    $stats = app(DeckStats::class)->fromSections($sections, config('series.magic'));
    $groups = $stats->zone('main')['groups'];

    // Numeric strings become int array keys in PHP, so compare them as strings.
    expect(array_map('strval', array_keys($groups['mana_value']['values'])))->toBe(['0', '1', '3', '7+']);
    expect(array_keys($groups['type']['values']))->toBe(['Creature', 'Instant', 'Sorcery', 'Land']);
    expect($groups['color']['values'])->toBe(['Red' => 3, 'Colorless' => 1]);
    expect($stats->groupCount('main', 'mana_value', '0'))->toBe(1);
    expect($stats->groupCount('main', 'mana_value', '7+'))->toBe(1);
    expect($stats->groupCount('main', 'type', 'Land'))->toBe(2);
});

test('stats keep their values when encoded as JSON', function () {
    // Mana values 0, 1, 2 are int keys 0, 1, 2: a plain json_encode would turn them into a list and lose them.
    $sections = ['main' => collect([
        ...statsCards('magic', ['id' => 'a', 'type_line' => 'Artifact Creature — Thopter', 'cmc' => 0.0, 'colors' => []]),
        ...statsCards('magic', ['id' => 'b', 'type_line' => 'Creature — Goblin', 'cmc' => 1.0, 'colors' => ['R']], 2),
        ...statsCards('magic', ['id' => 'c', 'type_line' => 'Instant', 'cmc' => 2.0, 'colors' => ['R']]),
    ])];

    $json = json_decode(json_encode(app(DeckStats::class)->fromSections($sections, config('series.magic'))), true);

    expect($json['zones']['main']['groups']['mana_value']['values'])->toBe([
        ['value' => '0', 'count' => 1],
        ['value' => '1', 'count' => 2],
        ['value' => '2', 'count' => 1],
    ]);
    expect($json['zones']['main']['groups']['color']['values'])->toBe([
        ['value' => 'Red', 'count' => 3],
        ['value' => 'Colorless', 'count' => 1],
    ]);
    expect($json)->toHaveKeys(['price', 'currency', 'missingPrices']);
});

test('values outside the configured order come after it, in natural order', function () {
    $config = config('series.yugioh');
    $config['deck']['stats']['category']['order'] = ['Trap'];

    $sections = ['main' => collect([
        ...statsCards('yugioh', ['id' => 1, 'type' => 'Spell Card']),
        ...statsCards('yugioh', ['id' => 2, 'type' => 'Effect Monster', 'level' => 10, 'attribute' => 'DARK']),
        ...statsCards('yugioh', ['id' => 3, 'type' => 'Trap Card']),
        ...statsCards('yugioh', ['id' => 4, 'type' => 'Effect Monster', 'level' => 2, 'attribute' => 'DARK']),
        ...statsCards('yugioh', ['id' => 5, 'type' => 'Link Monster', 'linkval' => 1, 'attribute' => 'DARK']),
    ])];

    $groups = app(DeckStats::class)->fromSections($sections, $config)->zone('main')['groups'];

    expect(array_keys($groups['category']['values']))->toBe(['Trap', 'Monster', 'Spell']);
    expect(array_keys($groups['level']['values']))->toBe(['Level 2', 'Level 10', 'Link 1']);
});

test('the deck price adds up every copy and counts cards without a price', function () {
    $sections = [
        'main' => collect([
            ...statsCards('yugioh', ['id' => 1, 'type' => 'Spell Card', 'card_prices' => [['cardmarket_price' => '0.10']]], 3),
            ...statsCards('yugioh', ['id' => 2, 'type' => 'Spell Card', 'card_prices' => [['cardmarket_price' => 'N/A']]], 2),
        ]),
        'extra' => collect(statsCards('yugioh', ['id' => 3, 'type' => 'Link Monster', 'linkval' => 2, 'card_prices' => [['cardmarket_price' => '2.50']]])),
    ];

    $stats = app(DeckStats::class)->fromSections($sections, config('series.yugioh'));

    expect($stats->zone('main')['price'])->toEqualWithDelta(0.30, 0.0001);
    expect($stats->zone('extra')['price'])->toEqualWithDelta(2.50, 0.0001);
    expect($stats->price)->toEqualWithDelta(2.80, 0.0001);
    expect($stats->missingPrices)->toBe(2);
    expect($stats->currency)->toBe(['code' => 'EUR', 'symbol' => '€']);
});

test('a card the source no longer has is counted without groups or price', function () {
    $sections = ['main' => collect(statsCards('magic', ['id' => 'gone', 'name' => 'Gone'], 2))];

    $stats = app(DeckStats::class)->fromSections($sections, config('series.magic'));

    expect($stats->zone('main')['count'])->toBe(2);
    expect($stats->zone('main')['groups']['type']['values'])->toBe([]);
    expect($stats->missingPrices)->toBe(2);
});

test('zones the series does not have are ignored', function () {
    $sections = [
        'main' => collect(statsCards('magic', ['id' => 'a', 'type_line' => 'Instant', 'cmc' => 1.0, 'colors' => ['U'], 'prices' => ['eur' => '1.00']])),
        'commander' => collect(statsCards('magic', ['id' => 'b', 'type_line' => 'Legendary Creature — Elf', 'cmc' => 3.0, 'colors' => ['G'], 'prices' => ['eur' => '5.00']])),
    ];

    $stats = app(DeckStats::class)->fromSections($sections, config('series.magic'));

    expect(array_keys($stats->zones))->toBe(['main', 'side']);
    expect($stats->price)->toEqualWithDelta(1.00, 0.0001);
    expect($stats->missingPrices)->toBe(0);
});

test('the deck page gets the deck stats', function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_prices' => [['cardmarket_price' => '0.10']]])->create();
    $deck = Deck::factory()->public()->create(['game' => 'yugioh']);
    $card = Card::create(['game' => 'yugioh', 'external_id' => '46986414', 'name' => 'Dark Magician']);
    $deck->deckCards()->create(['card_id' => $card->id, 'zone' => 'main', 'count' => 3]);

    $this->get(route('decks.show', $deck))
        ->assertOk()
        ->assertViewHas('stats', fn (DeckStatsResult $stats) => $stats->zone('main')['count'] === 3
            && $stats->groupCount('main', 'category', 'Monster') === 3
            && abs($stats->price - 0.30) < 0.0001);
});

test('prices are shown in the stats currency with two decimals', function () {
    $stats = app(DeckStats::class)->fromSections(['main' => collect()], config('series.yugioh'));

    expect($stats->money(84.2))->toBe('€84.20')
        ->and($stats->money(1234.5))->toBe('€1,234.50');
});

test('the headline counts are the main deck in the first stats group', function () {
    $sections = ['main' => collect([
        ...statsCards('yugioh', ['id' => 1, 'type' => 'Normal Monster', 'level' => 4, 'attribute' => 'DARK'], 3),
        ...statsCards('yugioh', ['id' => 3, 'type' => 'Spell Card'], 2),
    ])];

    $stats = app(DeckStats::class)->fromSections($sections, config('series.yugioh'));

    expect($stats->headlineCounts())->toBe(['Monster' => 3, 'Spell' => 2])
        ->and(app(DeckStats::class)->fromSections(['main' => collect()], config('series.yugioh'))->headlineCounts())->toBe([]);
});

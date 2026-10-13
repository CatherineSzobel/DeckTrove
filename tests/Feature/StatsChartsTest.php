<?php

use App\Decks\DeckStats;
use App\Decks\DeckStatsResult;
use App\Decks\StatsCharts;
use App\ViewModels\CardViewModel;

/** Deck stats for a few Yu-Gi-Oh! cards: three Level 4 monsters, one Rank 4 XYZ, two spells. */
function chartStats(): DeckStatsResult
{
    $card = fn (array $data) => new CardViewModel($data, config('series.yugioh'));

    return app(DeckStats::class)->fromSections([
        'main' => collect([
            ...array_fill(0, 3, $card(['id' => 1, 'type' => 'Effect Monster', 'level' => 4, 'attribute' => 'DARK'])),
            ...array_fill(0, 2, $card(['id' => 2, 'type' => 'Spell Card'])),
        ]),
        'extra' => collect([$card(['id' => 3, 'type' => 'XYZ Monster', 'level' => 4, 'attribute' => 'LIGHT'])]),
    ], config('series.yugioh'));
}

test('each zone gets a short tab label, its price and one chart per stats group', function () {
    $zones = StatsCharts::for(chartStats(), 'yugioh')->zones();

    expect(array_column($zones, 'tab'))->toBe(['Main', 'Extra', 'Side'])
        ->and(array_column($zones, 'key'))->toBe(['main', 'extra', 'side'])
        ->and(array_column($zones[0]['charts'], 'kind'))->toBe(['stacked', 'columns', 'bars'])
        ->and($zones[2]['count'])->toBe(0);
});

test('chart values carry their colour (accent by default), short label and size', function () {
    $main = StatsCharts::for(chartStats(), 'yugioh')->zones()[0];
    [$types, $levels, $attributes] = $main['charts'];

    expect($types['values'][0])->toMatchArray(['value' => 'Monster', 'count' => 3, 'colour' => '#B8652F'])
        ->and($levels['values'][0])->toMatchArray(['value' => 'Level 4', 'short' => '4', 'percent' => 100])
        ->and($attributes['values'][0])->toMatchArray(['value' => 'DARK', 'colour' => 'var(--color-accent)', 'percent' => 100])
        ->and(StatsCharts::for(chartStats(), 'yugioh')->zones()[1]['charts'][1]['values'][0]['short'])->toBe('R4');
});

test('a series with two zones keeps the full name of the second one', function () {
    $stats = app(DeckStats::class)->fromSections(['main' => collect()], config('series.magic'));

    expect(array_column(StatsCharts::for($stats, 'magic')->zones(), 'tab'))->toBe(['Main', 'Sideboard']);
});

test('the note says how many cards have no price', function () {
    $stats = chartStats();

    expect(StatsCharts::for($stats, 'yugioh')->priceNote())->toBe('6 cards have no price · Cardmarket prices in euros');
});

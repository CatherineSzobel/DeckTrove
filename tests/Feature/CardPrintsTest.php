<?php

use App\Services\YugiohService;
use App\ViewModels\CardViewModel;

test('a card lists each printing from the card source, linked to its pack page', function () {
    $raw = ['id' => 1, 'name' => 'Dark Magician', 'card_sets' => [
        ['set_code' => 'LOB-EN005', 'set_name' => 'Legend of Blue Eyes White Dragon'],
        ['set_code' => 'SDY-006', 'set_name' => 'Starter Deck: Yugi'],
        ['set_code' => '', 'set_name' => 'No code'],
    ]];
    $card = new CardViewModel($raw, config('series.yugioh'));

    $rows = $card->printRows('yugioh', app(YugiohService::class)->printings($raw));

    expect($rows->map(fn (array $row) => [$row['code'], $row['url'], $row['count']])->all())->toBe([
        ['LOB-EN005', route('packs.show', ['yugioh', 'LOB', 'legend-of-blue-eyes-white-dragon']), 0],
        ['SDY-006', route('packs.show', ['yugioh', 'SDY', 'starter-deck-yugi']), 0],
    ]);
});

test('a player sees their copies per printing, and a printing the source dropped last without a link', function () {
    $raw = ['id' => 1, 'name' => 'Dark Magician', 'card_sets' => [['set_code' => 'LOB-EN005', 'set_name' => 'Legend of Blue Eyes White Dragon']]];
    $card = new CardViewModel($raw, config('series.yugioh'));

    $rows = $card->printRows('yugioh', app(YugiohService::class)->printings($raw), ['' => 2, 'LOB-EN005' => 1, 'OLD-001' => 3]);

    expect($rows->map(fn (array $row) => [$row['printing'], $row['count'], $row['url'] !== null])->all())->toBe([
        ['LOB-EN005', 1, true],
        ['OLD-001', 3, false],
    ]);
});

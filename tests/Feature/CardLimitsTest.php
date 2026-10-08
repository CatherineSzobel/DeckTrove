<?php

use App\Cards\MagicCardMapper;
use App\Cards\YugiohCardMapper;
use App\ViewModels\CardViewModel;

test('yugioh limits come from the banlist of the format', function (?string $status, ?int $limit) {
    $card = $status ? ['banlist_info' => ['ban_tcg' => $status]] : [];

    expect(app(YugiohCardMapper::class)->copyLimit($card, 'tcg'))->toBe($limit);
})->with([
    'forbidden' => ['Forbidden', 0],
    'limited' => ['Limited', 1],
    'semi-limited' => ['Semi-Limited', 2],
    'not on the banlist' => [null, null],
]);

test('a yugioh banlist entry only applies to its own format', function () {
    $card = ['banlist_info' => ['ban_ocg' => 'Forbidden']];

    expect(app(YugiohCardMapper::class)->copyLimit($card, 'tcg'))->toBeNull();
    expect(app(YugiohCardMapper::class)->copyLimit($card, 'ocg'))->toBe(0);
});

test('magic limits come from the legality in the format', function (?string $legality, ?int $limit) {
    $card = $legality ? ['legalities' => ['modern' => $legality]] : ['id' => 'x', 'name' => 'No legalities'];

    expect(app(MagicCardMapper::class)->copyLimit($card, 'modern'))->toBe($limit);
})->with([
    'banned' => ['banned', 0],
    'not legal' => ['not_legal', 0],
    'restricted' => ['restricted', 1],
    'legal' => ['legal', null],
    'no legalities' => [null, null],
]);

test('the view model lists only the formats that limit a card', function () {
    $card = new CardViewModel(['id' => 1, 'name' => 'Graceful Charity', 'banlist_info' => ['ban_tcg' => 'Limited']], config('series.yugioh'));

    expect($card->copyLimit('tcg'))->toBe(1);
    expect($card->copyLimit('ocg'))->toBeNull();
    expect($card->copyLimits())->toBe(['tcg' => 1]);
});

test('a card without limits has none in any format', function () {
    $card = new CardViewModel(['id' => 'x', 'name' => 'Forest', 'legalities' => ['standard' => 'legal', 'modern' => 'legal']], config('series.magic'));

    expect($card->copyLimits())->toBe([]);
});

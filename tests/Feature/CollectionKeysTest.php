<?php

use App\ViewModels\CardViewModel;

test('a Yu-Gi-Oh! card is collected by its passcode', function () {
    $card = new CardViewModel(['id' => 46986414, 'name' => 'Dark Magician'], config('series.yugioh'));

    expect($card->collectionKey())->toBe('46986414');
});

test('Magic printings of one card share its oracle id', function () {
    $m10 = new CardViewModel(['id' => 'aaa', 'oracle_id' => 'bolt-oracle', 'name' => 'Lightning Bolt'], config('series.magic'));
    $x2 = new CardViewModel(['id' => 'bbb', 'oracle_id' => 'bolt-oracle', 'name' => 'Lightning Bolt'], config('series.magic'));
    $faces = new CardViewModel(['id' => 'ccc', 'card_faces' => [['oracle_id' => 'face-oracle']]], config('series.magic'));
    $bare = new CardViewModel(['id' => 'ddd', 'name' => 'Gone'], config('series.magic'));

    expect($m10->collectionKey())->toBe('bolt-oracle')
        ->and($x2->collectionKey())->toBe('bolt-oracle')
        ->and($faces->collectionKey())->toBe('face-oracle')
        ->and($bare->collectionKey())->toBe('ddd');
});

test('a Yu-Gi-Oh! card added from a set records that set’s print code', function () {
    $card = new CardViewModel(['id' => 46986414, 'card_sets' => [
        ['set_code' => 'SDY-EN006', 'set_name' => 'Starter Deck: Yugi'],
        ['set_code' => 'LOB-EN005', 'set_name' => 'Legend of Blue Eyes White Dragon'],
        ['set_code' => 'LOB-E005', 'set_name' => 'Legend of Blue Eyes White Dragon (EU)'],
    ]], config('series.yugioh'));

    expect($card->printingIn('LOB', 'Legend of Blue Eyes White Dragon'))->toBe('LOB-EN005')
        ->and($card->printingIn('lob'))->toBe('LOB-EN005')
        ->and($card->printingIn('MRD'))->toBe('');
});

test('a Magic card added from a set records its own printing', function () {
    $card = new CardViewModel(['id' => 'aaa', 'set' => 'm10'], config('series.magic'));

    expect($card->printingIn('m10'))->toBe('aaa');
});

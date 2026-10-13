<?php

use App\ViewModels\CardViewModel;

test('a card lists each set it was printed in once, linked to the pack page', function () {
    $card = new CardViewModel(['id' => 1, 'name' => 'Dark Magician', 'card_sets' => [
        ['set_code' => 'LOB-EN005', 'set_name' => 'Legend of Blue Eyes White Dragon'],
        ['set_code' => 'LOB-E005', 'set_name' => 'Legend of Blue Eyes White Dragon'],
        ['set_code' => 'SDY-006', 'set_name' => 'Starter Deck: Yugi'],
        ['set_code' => '', 'set_name' => 'No code'],
    ]], config('series.yugioh'));

    expect($card->printPacks('yugioh')->all())->toBe([
        ['set_name' => 'Legend of Blue Eyes White Dragon', 'code' => 'LOB', 'url' => route('packs.show', ['yugioh', 'LOB', 'legend-of-blue-eyes-white-dragon'])],
        ['set_name' => 'Starter Deck: Yugi', 'code' => 'SDY', 'url' => route('packs.show', ['yugioh', 'SDY', 'starter-deck-yugi'])],
    ]);
});

<?php

use App\ViewModels\PackViewModel;

function packFor(string $series, array $pack): PackViewModel
{
    return new PackViewModel($pack, config("series.$series.pack"));
}

test('codes are upper case and dates read like Mar 8, 2002', function () {
    $yugioh = packFor('yugioh', ['set_code' => 'LOB', 'set_name' => 'Legend', 'tcg_date' => '2002-03-08']);
    $magic = packFor('magic', ['code' => 'dom', 'name' => 'Dominaria', 'released_at' => '2018-04-27']);

    expect($yugioh->codeLabel())->toBe('LOB')
        ->and($yugioh->releaseLabel())->toBe('Mar 8, 2002')
        ->and($magic->codeLabel())->toBe('DOM')
        ->and($magic->releaseLabel())->toBe('Apr 27, 2018');
});

test('missing or broken dates have no label', function () {
    expect(packFor('yugioh', ['set_code' => 'X'])->releaseLabel())->toBeNull()
        ->and(packFor('yugioh', ['set_code' => 'X', 'tcg_date' => 'soon'])->releaseLabel())->toBeNull();
});

test('magic set types read as words; yugioh has none', function () {
    expect(packFor('magic', ['code' => 'x', 'set_type' => 'draft_innovation'])->typeLabel())->toBe('Draft innovation')
        ->and(packFor('yugioh', ['set_code' => 'X'])->typeLabel())->toBeNull();
});

test('facts list code, type, cards and release, leaving out empty ones', function () {
    expect(packFor('magic', ['code' => 'dom', 'set_type' => 'expansion', 'card_count' => 280, 'released_at' => '2018-04-27'])->facts())
        ->toBe([
            ['label' => 'Code', 'value' => 'DOM'],
            ['label' => 'Type', 'value' => 'Expansion'],
            ['label' => 'Cards', 'value' => '280'],
            ['label' => 'Released', 'value' => 'Apr 27, 2018'],
        ])
        ->and(packFor('yugioh', ['set_code' => 'LOB', 'num_of_cards' => null])->facts())
        ->toBe([['label' => 'Code', 'value' => 'LOB']]);
});

test('the meta line leaves out what is missing', function () {
    expect(packFor('yugioh', ['set_code' => 'LOB', 'tcg_date' => '2002-03-08'])->metaLine())->toBe('LOB · Released Mar 8, 2002')
        ->and(packFor('yugioh', ['set_code' => 'LOB'])->metaLine())->toBe('LOB')
        ->and(packFor('yugioh', [])->metaLine())->toBe('');
});

test('the cover is art, a symbol or a code tile', function () {
    expect(packFor('yugioh', ['set_code' => 'LOB', 'set_image' => 'https://images.ygoprodeck.com/images/sets/LOB.jpg'])->coverKind())->toBe('art')
        ->and(packFor('yugioh', ['set_code' => 'LOB'])->coverKind())->toBe('code')
        ->and(packFor('magic', ['code' => 'dom', 'icon_svg_uri' => 'https://svgs.scryfall.io/sets/dom.svg'])->coverKind())->toBe('symbol')
        ->and(packFor('magic', ['code' => 'dom'])->coverKind())->toBe('code');
});

test('a pack always gets the same tile colour', function () {
    $tone = packFor('yugioh', ['set_code' => 'LOB'])->tone();

    expect($tone)->toBeGreaterThanOrEqual(0)->toBeLessThan(6)
        ->and(packFor('yugioh', ['set_code' => 'LOB'])->tone())->toBe($tone);
});

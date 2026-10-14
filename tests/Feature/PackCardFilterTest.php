<?php

use App\Packs\PackCardFilter;
use App\ViewModels\CardCollectionViewModel;
use App\ViewModels\CardViewModel;
use Illuminate\Http\Request;

function filterFrom(array $params): PackCardFilter
{
    return PackCardFilter::fromRequest(Request::create('/yugioh/pack/LOB', 'GET', $params));
}

function packCard(string $name, string $rarity): CardViewModel
{
    return new CardViewModel(['name' => $name, 'printing' => ['set_rarity' => $rarity]], config('series.yugioh'));
}

test('the filter reads a trimmed name and a rarity, ignoring empty and array values', function () {
    $filter = filterFrom(['name' => '  dark ', 'rarity' => 'Ultra Rare']);

    expect($filter->name())->toBe('dark')
        ->and($filter->rarity())->toBe('Ultra Rare')
        ->and($filter->isFiltering())->toBeTrue()
        ->and(filterFrom(['name' => ['x'], 'rarity' => ''])->isFiltering())->toBeFalse()
        ->and(mb_strlen(filterFrom(['name' => str_repeat('a', 150)])->name()))->toBe(100);
});

test('cards match by part of the name, ignoring case, and by exact rarity', function () {
    $dark = packCard('Dark Magician', 'Ultra Rare / Secret Rare');
    $elf = packCard('Émile the Elf', 'Common');

    expect(filterFrom(['name' => 'MAGIC'])->matches($dark))->toBeTrue()
        ->and(filterFrom(['name' => 'émile'])->matches($elf))->toBeTrue()
        ->and(filterFrom(['rarity' => 'Ultra Rare / Secret Rare'])->matches($dark))->toBeTrue()
        ->and(filterFrom(['rarity' => 'Ultra Rare'])->matches($dark))->toBeFalse()
        ->and(filterFrom(['name' => 'dark', 'rarity' => 'Common'])->matches($dark))->toBeFalse()
        ->and(filterFrom([])->matches($elf))->toBeTrue()
        ->and(filterFrom(['name' => 'dark'])->count(collect([$dark, $elf])))->toBe(1);
});

test('the count reads "N cards", or "shown of total" while filtering', function () {
    expect(filterFrom([])->countLabel(2, 2))->toBe('2 cards')
        ->and(filterFrom([])->countLabel(1, 1))->toBe('1 card')
        ->and(filterFrom([])->countLabel(1200, 1200))->toBe('1,200 cards')
        ->and(filterFrom(['name' => 'dark'])->countLabel(1, 2))->toBe('1 of 2 cards')
        ->and(filterFrom(['name' => 'zzz'])->countLabel(0, 1))->toBe('0 of 1 card');
});

test('the selected rarity is recognised', function () {
    expect(filterFrom(['rarity' => 'common'])->isRarity('common'))->toBeTrue()
        ->and(filterFrom(['rarity' => 'common'])->isRarity('rare'))->toBeFalse()
        ->and(filterFrom([])->isRarity('common'))->toBeFalse();
});

test('rarity options keep the raw value and show it capitalised', function () {
    $magic = fn (string $rarity) => new CardViewModel(['name' => 'x', 'rarity' => $rarity], config('series.magic'));

    expect((new CardCollectionViewModel(collect([$magic('rare'), $magic('common'), $magic('rare')])))->rarityOptions())
        ->toBe(['common' => 'Common', 'rare' => 'Rare']);
});

test('a card has a lower case name for searching', function () {
    expect(packCard('Dark Magician', 'Common')->searchName())->toBe('dark magician');
});

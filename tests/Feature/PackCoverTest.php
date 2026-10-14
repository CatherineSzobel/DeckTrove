<?php

use App\ViewModels\PackViewModel;
use Illuminate\Support\Facades\Blade;

function coverPack(string $series, array $pack): PackViewModel
{
    return new PackViewModel($pack, config("series.$series.pack"));
}

test('a pack with art shows its image', function () {
    $html = Blade::render('<x-pack.cover :pack="$pack" size="lg" />', [
        'pack' => coverPack('yugioh', ['set_code' => 'LOB', 'set_image' => 'https://images.ygoprodeck.com/images/sets/LOB.jpg']),
    ]);

    expect($html)->toContain('class="pack-cover" data-size="lg" data-kind="art"')
        ->toContain('aria-hidden="true"')
        ->toContain('<img src="https://images.ygoprodeck.com/images/sets/LOB.jpg" alt=""')
        ->not->toContain('pack-cover-code');
});

test('a pack without art is a coloured tile with its code', function () {
    $html = Blade::render('<x-pack.cover :pack="$pack" />', ['pack' => coverPack('yugioh', ['set_code' => 'LOB'])]);

    expect($html)->toContain('data-size="sm" data-kind="code"')
        ->toMatch('/data-tone="[0-5]"/')
        ->toContain('<span class="pack-cover-code">LOB</span>')
        ->not->toContain('<img');
});

test('a magic set shows its symbol in a circle on a tile, with its code', function () {
    $html = Blade::render('<x-pack.cover :pack="$pack" />', [
        'pack' => coverPack('magic', ['code' => 'dom', 'icon_svg_uri' => 'https://svgs.scryfall.io/sets/dom.svg']),
    ]);

    expect($html)->toContain('data-kind="symbol"')
        ->toContain('<span class="pack-cover-symbol"><img src="https://svgs.scryfall.io/sets/dom.svg" alt=""')
        ->toContain('<span class="pack-cover-code">DOM</span>');
});

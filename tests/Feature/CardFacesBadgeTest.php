<?php

use App\ViewModels\CardViewModel;
use Illuminate\Support\Facades\Blade;

function badgeCard(array $card): CardViewModel
{
    return new CardViewModel($card, config('series.magic'));
}

test('a double-faced card’s tile says it has two faces', function () {
    $face = fn (string $name) => ['name' => $name, 'image_uris' => ['normal' => "https://cards.scryfall.io/normal/$name.jpg"]];

    $html = Blade::render('<x-card-faces-badge :card="$card" />', ['card' => badgeCard(['card_faces' => [$face('front'), $face('back')]])]);

    expect($html)->toContain('2 faces')->toContain('Double-faced card');
});

test('a single-faced card’s tile has no badge', function () {
    expect(trim(Blade::render('<x-card-faces-badge :card="$card" />', ['card' => badgeCard(['name' => 'Llanowar Elves'])])))->toBe('');
});

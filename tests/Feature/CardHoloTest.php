<?php

use App\Models\YugiohCard;
use App\ViewModels\CardViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/** A raw card of this series with this rarity (Yu-Gi-Oh!: its first printing's; Magic: Scryfall's `rarity`). */
function holoCard(string $series, ?string $rarity): CardViewModel
{
    $raw = match ($series) {
        'yugioh' => ['id' => 1, 'name' => 'Card', 'card_sets' => $rarity === null ? [] : [['set_name' => 'Set', 'set_code' => 'SET-EN001', 'set_rarity' => $rarity]]],
        'magic' => array_filter(['id' => 'card', 'name' => 'Card', 'rarity' => $rarity]),
    };

    return new CardViewModel($raw, config("series.$series"));
}

test('yugioh foil rarities are holo', function (string $rarity, bool $holo) {
    expect(holoCard('yugioh', $rarity)->isHolo())->toBe($holo);
})->with([
    ['Super Rare', true],
    ['Ultra Rare', true],
    ['Starlight Rare', true],
    ['Quarter Century Secret Rare', true],
    ['Duel Terminal Ultra Parallel Rare', true],
    ['Super Parallel Rare', true],
    ["Collector's Rare", true],
    ['PLatinum Secret Rare', true],
    ['Common', false],
    ['Rare', false],
    ['Short Print', false],
    ['Super Short Print', false],
    ['New', false],
]);

test('magic rares and up are holo', function (string $rarity, bool $holo) {
    expect(holoCard('magic', $rarity)->isHolo())->toBe($holo);
})->with([
    ['rare', true],
    ['mythic', true],
    ['special', true],
    ['bonus', true],
    ['common', false],
    ['uncommon', false],
]);

test('a card without a rarity, or a series without holo rarities, is not holo', function () {
    expect(holoCard('yugioh', null)->isHolo())->toBeFalse()
        ->and(holoCard('magic', null)->isHolo())->toBeFalse();

    config(['series.yugioh' => collect(config('series.yugioh'))->except('holo_rarities')->all()]);

    expect(holoCard('yugioh', 'Secret Rare')->isHolo())->toBeFalse();
});

test('holo rarities are lists of words', function () {
    foreach (['yugioh', 'magic'] as $series) {
        $words = config("series.$series.holo_rarities");

        expect($words)->toBeArray()->not->toBeEmpty()
            ->and(array_is_list($words))->toBeTrue()
            ->and(collect($words)->every(fn ($word) => is_string($word) && $word !== ''))->toBeTrue();
    }
});

/** The card page's HTML for a Yu-Gi-Oh! card whose only printing has this rarity. */
function cardPageHtml(string $rarity): string
{
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_sets' => [
        ['set_name' => 'Legend of Blue Eyes White Dragon', 'set_code' => 'LOB-EN005', 'set_rarity' => $rarity],
    ]])->create();

    return test()->get(route('cards.show', ['yugioh', '46986414']))->assertOk()->getContent();
}

/** Fakes Scryfall's card endpoint with a double-faced card. */
function fakeScryfallHoloCard(): void
{
    Http::fake([
        'api.scryfall.com/cards/search*' => Http::response(['data' => [], 'total_cards' => 0, 'has_more' => false]),
        'api.scryfall.com/cards/*' => Http::response([
            'id' => '22222222-2222-4222-8222-222222222222', 'name' => 'Delver of Secrets // Insectile Aberration',
            'set' => 'isd', 'set_name' => 'Innistrad', 'rarity' => 'common', 'type_line' => 'Creature — Human Wizard',
            'card_faces' => [
                ['name' => 'Delver of Secrets', 'image_uris' => ['normal' => 'https://cards.scryfall.io/normal/front.jpg']],
                ['name' => 'Insectile Aberration', 'image_uris' => ['normal' => 'https://cards.scryfall.io/normal/back.jpg']],
            ],
        ]),
    ]);
}

test('the big card tilts, with the foil sheen only for holo rarities', function () {
    expect(cardPageHtml('Ultra Rare'))
        ->toMatch('/<div x-data="cardTilt" @pointermove="move\(\$event\)" @pointerleave="reset\(\)" class="card-holo"\s+data-holo\s*>\s*<img /');
});

test('a common card tilts without the foil sheen', function () {
    expect(cardPageHtml('Common'))
        ->toMatch('/class="card-holo"\s*>\s*<img /')
        ->not->toContain('data-holo');
});

test('a double-faced card keeps its face button outside the tilting card', function () {
    fakeScryfallHoloCard();

    expect($this->get(route('cards.show', ['magic', '22222222-2222-4222-8222-222222222222']))->assertOk()->getContent())
        ->toMatch('/class="card-holo"\s*>\s*<img [^>]*:src="current"[^>]*>\s*<\/div>\s*<button type="button" @click="next\(\)"/');
});

test('the tilt and glare only run with a hovering pointer and without reduced motion', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toMatch('/@media \(prefers-reduced-motion: no-preference\) and \(hover: hover\)\s*\{[^@]*rotateX\(var\(--tilt-x\)\) rotateY\(var\(--tilt-y\)\)/')
        ->and(substr_count($css, 'rotateX(var(--tilt-x))'))->toBe(1)
        ->and($css)->toContain('&[data-holo]::after');
});

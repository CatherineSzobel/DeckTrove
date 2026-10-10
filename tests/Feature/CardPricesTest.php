<?php

use App\Cards\MagicCardMapper;
use App\Cards\YugiohCardMapper;
use App\Models\YugiohCard;
use App\ViewModels\CardViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('yugioh prices are read as numbers from cardmarket', function (array $card, ?float $value) {
    expect(app(YugiohCardMapper::class)->priceValue($card))->toBe($value);
})->with([
    'a price' => [['card_prices' => [['cardmarket_price' => '0.10']]], 0.1],
    'a larger price' => [['card_prices' => [['cardmarket_price' => '12.50']]], 12.5],
    'n/a' => [['card_prices' => [['cardmarket_price' => 'N/A']]], null],
    'empty' => [['card_prices' => [['cardmarket_price' => '']]], null],
    'no prices' => [['id' => 1, 'name' => 'Gone'], null],
]);

test('magic prices come from scryfall in euros', function () {
    $mapper = app(MagicCardMapper::class);
    $card = ['prices' => ['usd' => '1.00', 'eur' => '0.85']];

    expect($mapper->price($card))->toBe('0.85');
    expect($mapper->priceValue($card))->toBe(0.85);
});

test('a magic card with only a dollar price has no price', function () {
    expect(app(MagicCardMapper::class)->priceValue(['prices' => ['usd' => '1.00', 'eur' => null]]))->toBeNull();
});

test('the view model shows prices with the currency symbol', function () {
    $priced = new CardViewModel(['id' => 1, 'name' => 'A', 'card_prices' => [['cardmarket_price' => '1234.5']]], config('series.yugioh'));
    $unpriced = new CardViewModel(['id' => 2, 'name' => 'B'], config('series.yugioh'));

    expect(config('app.currency'))->toBe(['code' => 'EUR', 'symbol' => '€']);
    expect($priced->priceValue())->toBe(1234.5);
    expect($priced->formattedPrice())->toBe('€1,234.50');
    expect($unpriced->formattedPrice())->toBe('N/A');
});

test('card pages show prices in euros', function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_prices' => [['cardmarket_price' => '0.10']]])->create();

    $this->get(route('cards.show', ['yugioh', '46986414']))->assertOk()->assertSee('€0.10');
    $this->get(route('cards.index', 'yugioh'))->assertOk()->assertSee('€0.10')->assertDontSee('$0.10');
    $this->get(route('cards.index', ['yugioh', 'view' => 'list']))->assertOk()->assertSee('€0.10')->assertDontSee('$0.10');
});

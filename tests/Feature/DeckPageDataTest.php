<?php

use App\Models\Card;
use App\Models\Deck;
use App\Models\YugiohCard;
use App\ViewModels\CardViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('builder cards carry what the deck builder needs', function () {
    $card = new CardViewModel([
        'id' => 10000002, 'name' => 'Graceful Charity', 'type' => 'Spell Card', 'race' => 'Normal', 'desc' => 'Draw 3.',
        'card_prices' => [['cardmarket_price' => '1.25']], 'banlist_info' => ['ban_tcg' => 'Limited'],
    ], config('series.yugioh'));

    expect($card->builderCard())->toMatchArray([
        'id' => '10000002', 'name' => 'Graceful Charity', 'type' => 'Spell Card Normal', 'description' => 'Draw 3.',
        'price' => 1.25, 'limits' => ['tcg' => 1],
    ])->toHaveKeys(['image', 'imageSmall']);
});

test('a card the source lost still makes a builder card', function () {
    $card = new CardViewModel(['id' => 'gone', 'name' => 'Gone'], config('series.magic'));

    expect($card->builderCard())->toMatchArray(['id' => 'gone', 'name' => 'Gone', 'price' => null, 'limits' => []]);
});

test('the deck page gets the format problems', function () {
    YugiohCard::factory()->fromCard(['id' => 10000001, 'name' => 'Pot of Greed', 'type' => 'Spell Card', 'banlist_info' => ['ban_tcg' => 'Forbidden']])->create();
    $deck = Deck::factory()->public()->create(['game' => 'yugioh', 'format' => 'tcg']);
    $card = Card::create(['game' => 'yugioh', 'external_id' => '10000001', 'name' => 'Pot of Greed']);
    $deck->deckCards()->create(['card_id' => $card->id, 'zone' => 'main', 'count' => 1]);

    $this->get(route('decks.show', $deck))->assertOk()->assertViewHas('formatErrors', [
        'messages' => ['Pot of Greed is not allowed in TCG Advanced.'],
        'card_ids' => ['10000001'],
    ]);
});

test('alpine starts after the page scripts have registered their components', function () {
    $js = file_get_contents(resource_path('js/layout.js'));

    expect($js)->toContain('document.addEventListener("DOMContentLoaded", () => Alpine.start())')
        ->not->toMatch('/^Alpine\.start\(\);$/m');
});

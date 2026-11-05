<?php

use App\Services\MagicService;
use App\Services\YugiohService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function fakeMagicPrints(): void
{
    Http::fake(['api.scryfall.com/cards/search*' => Http::response(['data' => [
        ['id' => 'aaa', 'oracle_id' => 'bolt', 'set' => 'm10', 'set_name' => 'Magic 2010', 'collector_number' => '146'],
        ['id' => 'bbb', 'oracle_id' => 'bolt', 'set' => '2x2', 'set_name' => 'Double Masters 2022', 'collector_number' => '117'],
    ], 'has_more' => false])]);
}

test('Yu-Gi-Oh! printings are the card’s print codes, once each', function () {
    $printings = app(YugiohService::class)->printings(['id' => 1, 'card_sets' => [
        ['set_code' => 'LOB-EN005', 'set_name' => 'Legend of Blue Eyes White Dragon'],
        ['set_code' => 'LOB-EN005', 'set_name' => 'Legend of Blue Eyes White Dragon'],
        ['set_code' => 'SDY-EN006', 'set_name' => 'Starter Deck: Yugi'],
    ]]);

    expect($printings)->toBe([
        ['key' => 'LOB-EN005', 'label' => 'Legend of Blue Eyes White Dragon (LOB-EN005)', 'set_name' => 'Legend of Blue Eyes White Dragon', 'code' => 'LOB-EN005', 'pack_code' => 'LOB'],
        ['key' => 'SDY-EN006', 'label' => 'Starter Deck: Yugi (SDY-EN006)', 'set_name' => 'Starter Deck: Yugi', 'code' => 'SDY-EN006', 'pack_code' => 'SDY'],
    ]);
});

test('Magic printings come from Scryfall by oracle id', function () {
    fakeMagicPrints();

    $printings = app(MagicService::class)->printings(['id' => 'aaa', 'oracle_id' => 'bolt']);

    expect($printings)->toBe([
        ['key' => 'aaa', 'label' => 'Magic 2010 (M10 #146)', 'set_name' => 'Magic 2010', 'code' => 'M10 #146', 'pack_code' => 'm10'],
        ['key' => 'bbb', 'label' => 'Double Masters 2022 (2X2 #117)', 'set_name' => 'Double Masters 2022', 'code' => '2X2 #117', 'pack_code' => '2x2'],
    ]);
    Http::assertSent(fn ($request) => str_contains(urldecode($request->url()), 'oracleid:bolt unique:prints'));
});

test('a Magic card without an oracle id offers only itself', function () {
    Http::fake();

    expect(app(MagicService::class)->printings(['id' => 'zzz', 'set' => 'lea', 'set_name' => 'Alpha', 'collector_number' => '1']))
        ->toBe([['key' => 'zzz', 'label' => 'Alpha (LEA #1)', 'set_name' => 'Alpha', 'code' => 'LEA #1', 'pack_code' => 'lea']]);
    Http::assertNothingSent();
});

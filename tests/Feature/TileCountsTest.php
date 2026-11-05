<?php

use App\Collection\CollectionService;
use App\Collection\TileCounts;
use App\Models\User;
use App\Models\YugiohCard;
use App\ViewModels\CardViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function tileCountsCard(int $id, string $name): CardViewModel
{
    $raw = ['id' => $id, 'name' => $name, 'card_sets' => [['set_name' => 'Set', 'set_code' => "SET-EN$id", 'set_rarity' => 'Common']]];
    YugiohCard::factory()->fromCard($raw)->create();

    return new CardViewModel($raw, config('series.yugioh'));
}

test('every tile’s owned count comes from one query', function () {
    $user = User::factory()->create();
    $cards = collect([tileCountsCard(1, 'One'), tileCountsCard(2, 'Two'), tileCountsCard(3, 'Three')]);
    $service = app(CollectionService::class);
    $service->add($user, 'yugioh', '1', 'owned', '', 2);
    $service->add($user, 'yugioh', '2', 'owned', 'SET-EN2');

    DB::enableQueryLog();
    $counts = TileCounts::for($user, 'yugioh', $cards);
    $queries = count(DB::getQueryLog());

    expect($queries)->toBe(1)
        ->and($counts->shows())->toBeTrue()
        ->and([$counts->count($cards[0]), $counts->removable($cards[0])])->toBe([2, true])
        ->and([$counts->count($cards[1]), $counts->removable($cards[1])])->toBe([1, false])
        ->and([$counts->count($cards[2]), $counts->removable($cards[2])])->toBe([0, false]);
});

test('guests get no counts and no query', function () {
    $cards = collect([tileCountsCard(1, 'One')]);

    DB::enableQueryLog();
    $counts = TileCounts::for(null, 'yugioh', $cards);

    expect(DB::getQueryLog())->toBe([])
        ->and($counts->shows())->toBeFalse()
        ->and($counts->count($cards[0]))->toBe(0);
});

test('wanted copies and other games don’t count as owned', function () {
    $user = User::factory()->create();
    $cards = collect([tileCountsCard(1, 'One')]);
    app(CollectionService::class)->add($user, 'yugioh', '1', 'wanted');

    expect(app(CollectionService::class)->ownedCounts($user, 'yugioh', [$cards[0]->collectionKey()]))->toBe([])
        ->and(app(CollectionService::class)->ownedCounts($user, 'magic', [$cards[0]->collectionKey()]))->toBe([]);
});

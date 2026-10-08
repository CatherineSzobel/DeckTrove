<?php

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('the same card cannot be stored twice for a game', function () {
    Card::create(['game' => 'yugioh', 'external_id' => '123', 'name' => 'A']);

    expect(fn () => Card::create(['game' => 'yugioh', 'external_id' => '123', 'name' => 'B']))
        ->toThrow(UniqueConstraintViolationException::class);

    // The same id in another game is a different card.
    Card::create(['game' => 'magic', 'external_id' => '123', 'name' => 'C']);
    expect(Card::count())->toBe(2);
});

test('the migration merges existing duplicates before adding the unique index', function () {
    $this->artisan('migrate:rollback', ['--step' => 1])->assertSuccessful();

    $deck = Deck::factory()->create(['game' => 'yugioh']);
    $keep = DB::table('cards')->insertGetId(['game' => 'yugioh', 'external_id' => '123', 'name' => 'A']);
    $duplicate = DB::table('cards')->insertGetId(['game' => 'yugioh', 'external_id' => '123', 'name' => 'A']);
    DB::table('deck_cards')->insert(['deck_id' => $deck->id, 'card_id' => $duplicate, 'zone' => 'main', 'count' => 2]);

    $this->artisan('migrate')->assertSuccessful();

    expect(DB::table('cards')->where('external_id', '123')->pluck('id')->all())->toBe([$keep]);
    expect(DB::table('deck_cards')->where('deck_id', $deck->id)->value('card_id'))->toBe($keep);
});

test('saving decks reuses card rows and refreshes their details', function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
    Card::create(['game' => 'yugioh', 'external_id' => '46986414', 'name' => 'Old name']);
    $user = User::factory()->create();

    foreach (range(1, 2) as $i) {
        $this->actingAs($user)->post(route('decks.store', 'yugioh'), [
            'cards' => json_encode(['main' => [['id' => '46986414']]]),
        ])->assertSessionHasNoErrors();
    }

    expect(Card::where('external_id', '46986414')->count())->toBe(1);
    expect(Card::where('external_id', '46986414')->value('name'))->toBe('Dark Magician');
});

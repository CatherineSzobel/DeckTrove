<?php

use App\Models\User;
use App\Support\QueryInput;
use App\Support\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('a series is known only when it is a configured key', function (mixed $key, bool $known) {
    expect(Series::isKnown($key))->toBe($known);
})->with([
    'yugioh' => ['yugioh', true],
    'magic' => ['magic', true],
    'unknown' => ['pokemon', false],
    'empty' => ['', false],
    'null' => [null, false],
    'array' => [['yugioh'], false],
]);

test('an unknown series falls back to the first one, or to null', function () {
    expect(Series::default())->toBe(array_key_first(config('series')))
        ->and(Series::orDefault('magic'))->toBe('magic')
        ->and(Series::orDefault(['magic']))->toBe(Series::default())
        ->and(Series::orNull('magic'))->toBe('magic')
        ->and(Series::orNull('pokemon'))->toBeNull();
});

test('a query value is its string, or empty when missing or an array', function () {
    $request = Request::create('/x', 'GET', ['name' => 'Kuriboh', 'race' => ['Fiend']]);

    expect(QueryInput::string($request, 'name'))->toBe('Kuriboh')
        ->and(QueryInput::string($request, 'race'))->toBe('')
        ->and(QueryInput::string($request, 'missing'))->toBe('');
});

test('the dashboard ignores array series values instead of failing', function () {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard?tcg[]=yugioh&cards[]=magic')
        ->assertOk()->assertSessionMissing('series');
});

test('the packs page ignores an array view instead of failing', function () {
    $this->get('/yugioh/packs?view[]=full')->assertOk();
});

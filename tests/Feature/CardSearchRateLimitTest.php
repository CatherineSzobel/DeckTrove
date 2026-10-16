<?php

use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('card search routes are rate limited', function (string $name) {
    expect(Route::getRoutes()->getByName($name)->gatherMiddleware())->toContain('throttle:card-search');
})->with(['cards.index', 'cards.show', 'packs.show', 'decks.builder']);

test('guests are limited per IP and users per account', function () {
    $limiter = RateLimiter::limiter('card-search');

    $guest = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '10.0.0.1']);
    $member = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '10.0.0.1']);
    $member->setUserResolver(fn () => tap(new User)->forceFill(['id' => 7]));

    expect($limiter($guest)->maxAttempts)->toBe(60)
        ->and($limiter($guest)->key)->toBe('ip:10.0.0.1')
        ->and($limiter($member)->maxAttempts)->toBe(120)
        ->and($limiter($member)->key)->toBe('user:7');
});

test('going over the limit shows the slow down page, which retries the same search', function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
    $url = route('cards.index', ['yugioh', 'search' => 'dark']);

    foreach (range(1, 60) as $i) {
        $this->get($url)->assertOk();
    }

    $this->get($url)->assertStatus(429)
        ->assertSee('Slow down a little')
        ->assertSee('You’re searching faster than we can keep up with. Wait a minute, then try again.')
        ->assertSee('href="'.e($url).'" class="btn-primary">Try again</a>', false)
        ->assertSee('<nav aria-label="Main"', false);
});

test('the builder search gets a JSON 429 over the limit', function () {
    $user = User::factory()->create();
    // As the builder's fetch() sends them (its default Accept is */*).
    $headers = ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => '*/*'];

    foreach (range(1, 120) as $i) {
        $this->actingAs($user)->get(route('decks.builder', 'yugioh'), $headers)->assertOk();
    }

    $this->actingAs($user)->get(route('decks.builder', 'yugioh'), $headers)
        ->assertStatus(429)
        ->assertJsonStructure(['message']);
});

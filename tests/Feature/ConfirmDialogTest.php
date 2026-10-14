<?php

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;

uses(RefreshDatabase::class);

test('every page has the styled confirm dialog', function () {
    $this->get(route('public-deck'))->assertOk()->assertSee('data-confirm-dialog', false);
});

test('deleting a deck or the account asks in the styled dialog', function () {
    $user = User::factory()->create();
    Deck::factory()->for($user)->create(['name' => 'Old Deck']);

    $this->actingAs($user)->get(route('decks'))
        ->assertSee('data-confirm-title="Delete this deck?"', false)
        ->assertSee('data-confirm-button="Delete deck"', false);

    $this->actingAs($user)->get(route('settings'))
        ->assertSee('data-confirm-title="Delete your account?"', false)
        ->assertSee('data-confirm-button="Delete account"', false);
});

test('no page uses the browser confirm or alert pop-ups', function () {
    $files = Finder::create()->files()->in([resource_path('js'), resource_path('views')])->name(['*.js', '*.php'])->notName('*.test.js');
    $offenders = collect($files)->filter(fn ($file) => preg_match('/window\.(confirm|alert)\(/', $file->getContents()))->keys();

    expect($offenders->all())->toBe([]);
});

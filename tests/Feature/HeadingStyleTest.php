<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('big headings are in sentence case', function () {
    preg_match('/\.heading-display\s*\{([^}]*)\}/', file_get_contents(resource_path('css/app.css')), $rule);

    expect($rule[1])->toContain('font-display')->toContain('font-bold')->toContain('leading-none')
        ->not->toContain('uppercase')->not->toContain('font-extrabold');
});

test('the deck title input matches the headings', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('decks.builder', 'yugioh'))->getContent();

    preg_match('/<input id="deckTitle"[^>]*class="([^"]*)"/', $html, $input);

    expect($input[1])->toContain('font-display')->toContain('font-bold')
        ->not->toContain('uppercase')->not->toContain('font-extrabold');
});

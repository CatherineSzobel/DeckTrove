<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the dashboard gets its quick links from the service', function () {
    $this->actingAs(User::factory()->create())
        ->withSession(['series' => 'magic'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('quickLinks', [
            ['href' => route('cards.index', 'yugioh'), 'title' => 'Yu-Gi-Oh! cards'],
            ['href' => route('cards.index', 'magic'), 'title' => 'Magic: The Gathering cards'],
            ['href' => route('public-deck'), 'title' => 'Public Decks'],
            ['href' => route('decks.builder', 'magic'), 'title' => 'Deck Builder'],
        ])
        ->assertViewHas('builderSeries', 'magic');
});

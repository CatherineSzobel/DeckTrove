<?php

use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
});

// --- Tokens and fonts --------------------------------------------------------------------------

test('pages use the new colours and self-hosted fonts', function () {
    $this->get(route('public-deck'))->assertOk()
        ->assertSee('bg-canvas', false)
        ->assertDontSee('fonts.googleapis.com', false);

    $this->get('/')->assertOk()->assertDontSee('fonts.googleapis.com', false);
});

<?php

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** The look of a selected and an unselected segment, which now lives in the .segment class in app.css. */
const SEGMENT_STATE_CLASSES = ['bg-surface-2 font-bold text-ink', 'font-medium text-muted hover:text-ink'];

test('segmented controls use the segment class and mark the selected segment with an attribute', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->public()->for($user)->create(['game' => 'yugioh']);

    $pages = [
        'public decks' => route('decks.index', ['game' => 'yugioh']),
        'profile decks' => route('users.decks', $user),
        'my decks' => route('decks.mine'),
        'deck page' => route('decks.show', $deck),
        'cards (game switch)' => route('cards.index', 'yugioh'),
    ];

    foreach ($pages as $page => $url) {
        $html = $this->actingAs($user)->get($url)->assertOk()->getContent();

        expect(str_contains($html, 'class="segment'))->toBeTrue("$page has no .segment");
        foreach (SEGMENT_STATE_CLASSES as $classes) {
            expect(str_contains($html, $classes))->toBeFalse("$page still writes \"$classes\"");
        }
    }
});

test('the selected segment carries aria-current or aria-pressed', function () {
    $html = $this->get(route('decks.index', ['sort' => 'liked']))->getContent();

    expect($html)->toMatch('/<a[^>]*data-sort="liked"[^>]*aria-current="true"[^>]*class="segment/')
        ->not->toMatch('/<a[^>]*data-sort="newest"[^>]*aria-current/');
});

<?php

use App\ViewModels\CardViewModel;
use Illuminate\Support\Facades\Blade;

test('the format badge shows casual, legal and broken decks', function () {
    // The colours live in the .format-badge class in app.css, keyed by data-format-badge.
    expect(Blade::render('<x-deck.format-badge :format="null" />'))
        ->toContain('Casual')->toContain('data-format-badge="casual"')->toContain('class="format-badge')->not->toContain('bg-surface-2');
    expect(Blade::render('<x-deck.format-badge format="tcg" label="TCG Advanced" :problems="0" />'))
        ->toContain('TCG Advanced')->toContain('Legal in TCG Advanced.')->toContain('data-format-badge="legal"')->not->toContain('text-ok');
    expect(Blade::render('<x-deck.format-badge format="tcg" label="TCG Advanced" :problems="2" />'))
        ->toContain('This deck no longer fits TCG Advanced.')->toContain('data-format-badge="broken"')->not->toContain('text-danger');
});

test('a card slot marks cards that break the format', function () {
    $card = new CardViewModel(['id' => 1, 'name' => 'Pot of Greed'], config('series.yugioh'));

    $plain = Blade::render('<x-deck.card-slot :card="$card" />', ['card' => $card]);
    $over = Blade::render('<x-deck.card-slot :card="$card" over="Pot of Greed is not allowed in TCG Advanced." href="/card/1" />', ['card' => $card]);

    expect($plain)->toContain('alt="Pot of Greed"')->not->toContain('outline-danger');
    expect($over)->toContain('outline-danger')->toContain('title="Pot of Greed is not allowed in TCG Advanced."')
        ->toContain('href="/card/1"')->toContain('>!</span>');
});

test('a hovered or focused card slot sits above its neighbours so its preview is not covered', function () {
    $card = new CardViewModel(['id' => 1, 'name' => 'Pot of Greed'], config('series.yugioh'));

    expect(Blade::render('<x-deck.card-slot :card="$card" href="/card/1" />', ['card' => $card]))
        ->toContain('hover:z-20')->toContain('focus-visible:z-20');
});

test('the problems box lists the messages', function () {
    $html = Blade::render('<x-deck.problems title="2 cards break TCG Advanced" :messages="$m">Fix them.</x-deck.problems>', ['m' => ['A.', 'B.']]);

    expect($html)->toContain('role="alert"')->toContain('2 cards break TCG Advanced')
        ->toContain('<li>A.</li>')->toContain('<li>B.</li>')->toContain('Fix them.');
});

test('the empty state shows a title, an optional line and optional actions', function () {
    $full = Blade::render('<x-empty-state title="No decks match" heading="h3">Try another search.<x-slot:actions><a href="/decks">Go</a></x-slot:actions></x-empty-state>');

    expect($full)
        ->toContain('border-dashed')
        ->toMatch('/<h3[^>]*>No decks match<\/h3>/')
        ->toContain('Try another search.')
        ->toContain('<a href="/decks">Go</a>');

    expect(Blade::render('<x-empty-state title="Nothing here" x-show="open" />'))
        ->toMatch('/<h2[^>]*>Nothing here<\/h2>/')
        ->toContain('x-show="open"')
        ->not->toContain('<p');
});

test('a stat chip shows a number with its label', function () {
    $plain = Blade::render('<x-stat-chip :value="12" label="decks" />');
    expect($plain)
        ->toMatch('/<b class="font-mono text-base text-ink">12<\/b>\s*decks/')
        ->toContain('border-line')->toContain('bg-surface');

    expect(Blade::render('<x-stat-chip value="€84.20" label="deck price" accent warn inset title="2 cards have no price" />'))
        ->toContain('text-accent-ink')->toContain('border-warn-line')->toContain('bg-canvas')
        ->toContain('title="2 cards have no price"');

    expect(Blade::render('<x-stat-chip :value="40" label="cards" size="sm" class="absolute right-3 top-3" />'))
        ->toContain('text-xs')->toContain('absolute right-3 top-3')
        ->toMatch('/<b class="font-mono text-sm text-ink">40<\/b>\s*cards/');
});

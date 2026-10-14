<?php

use App\Home\HomePage;
use App\Models\User;

test('the eyebrow names the live games and promises more', function () {
    $labels = collect(config('series'))->pluck('label')->implode(' · ');

    expect(HomePage::for(null, null)->eyebrow())->toBe("$labels · more on the way");

    config(['coming_soon' => []]);
    expect(HomePage::for(null, null)->eyebrow())->toBe($labels);
});

test('games are numbered, live ones first with a link, coming soon ones without', function () {
    $games = HomePage::for(null, null)->games();
    $live = count(config('series'));

    expect(array_column($games, 'number'))->toBe(array_map(fn ($i) => sprintf('%02d', $i), range(1, $live + count(config('coming_soon')))))
        ->and($games[0])->toBe([
            'number' => '01',
            'label' => config('series.yugioh.label'),
            'tagline' => config('series.yugioh.tagline'),
            'live' => true,
            'href' => route('cards.index', 'yugioh'),
        ])
        ->and($games[$live]['live'])->toBeFalse()
        ->and($games[$live]['href'])->toBeNull()
        ->and($games[$live]['label'])->toBe('Pokémon');
});

test('the builder link follows the session series and falls back to the first one', function () {
    $first = array_key_first(config('series'));

    expect(HomePage::for(null, 'magic')->builderUrl())->toBe(route('decks.builder', 'magic'))
        ->and(HomePage::for(null, null)->builderUrl())->toBe(route('decks.builder', $first))
        ->and(HomePage::for(null, 'pokemon')->builderUrl())->toBe(route('decks.builder', $first));
});

test('guests are asked to sign up, users to build', function () {
    $guest = HomePage::for(null, 'magic');
    $user = HomePage::for(new User, 'magic');

    expect($guest->showGuestNote())->toBeTrue()
        ->and($guest->cta())->toBe([
            'title' => 'Your next deck starts here',
            'text' => 'Create an account to save decks and share them.',
            'primary' => ['label' => 'Create account', 'href' => route('register')],
            'secondary' => ['label' => 'Log in', 'href' => route('login')],
        ])
        ->and($user->showGuestNote())->toBeFalse()
        ->and($user->cta())->toBe([
            'title' => 'Your next deck starts here',
            'text' => 'Pick up where you left off, or start something new.',
            'primary' => ['label' => 'New deck', 'href' => route('decks.builder', 'magic')],
            'secondary' => ['label' => 'Go to your dashboard', 'href' => route('dashboard')],
        ]);
});

test('there are three numbered steps', function () {
    $steps = HomePage::for(null, null)->steps();

    expect(array_column($steps, 'number'))->toBe(['01', '02', '03'])
        ->and(array_column($steps, 'title'))->toBe(['Browse the database', 'Build against the rules', 'Share with the community'])
        ->and($steps[1]['text'])->toStartWith('Add cards to your deck');
});

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the home page has the header, the hero and its buttons', function () {
    expect($this->get('/')->assertOk()->getContent())
        ->toContain('data-nav="cards"')
        ->toContain('<title>DeckTrove</title>')
        ->toContain('Every card.<br>Every deck.<br><span class="text-accent-ink">One trove.</span>')
        ->toContain(e(collect(config('series'))->pluck('label')->implode(' · ')).' · more on the way')
        ->toContain('href="#games" class="btn-primary"')
        ->toContain('href="'.route('decks.builder', array_key_first(config('series'))).'" class="btn-secondary">Open the deck builder</a>')
        ->toContain('alt="DeckTrove card database showing Yu-Gi-Oh! search results"')
        ->toContain('Browsing cards is open to everyone — sign in to build and save decks.');
});

test('every live game is a tile linking to its cards, coming soon ones are not links', function () {
    $html = $this->get('/')->getContent();

    foreach (config('series') as $key => $config) {
        expect($html)->toContain('<a href="'.route('cards.index', $key).'" class="game-tile">')
            ->toContain(e($config['label']))
            ->toContain(e($config['tagline']));
    }

    foreach (config('coming_soon') as $config) {
        expect($html)->toContain(e($config['label']));
    }

    expect($html)->toContain('<div aria-disabled="true" class="game-tile">')
        ->toContain('>Live</span>')
        ->toContain('>Coming soon</span>')
        ->not->toContain('magic.png')
        ->not->toContain('yugioh.png');
});

test('the three steps are listed', function () {
    $this->get('/')
        ->assertSee('From first search to finished deck')
        ->assertSee('Browse the database')
        ->assertSee('Add cards to your deck')
        ->assertSee('Share with the community');
});

test('guests are asked to create an account', function () {
    $this->get('/')
        ->assertSee('Your next deck starts here')
        ->assertSee('href="'.route('register').'" class="btn-primary">Create account</a>', false)
        ->assertSee('href="'.route('login').'" class="btn-secondary">Log in</a>', false);
});

test('logged in users are asked to build, without sign up prompts', function () {
    $html = $this->actingAs(User::factory()->create())->withSession(['series' => 'magic'])->get('/')->getContent();

    expect($html)->toContain('href="'.route('decks.builder', 'magic').'" class="btn-primary">New deck</a>')
        ->toContain('href="'.route('dashboard').'" class="btn-secondary">Go to your dashboard</a>')
        ->not->toContain('Browsing cards is open to everyone')
        ->not->toContain('class="btn-primary">Create account</a>');
});

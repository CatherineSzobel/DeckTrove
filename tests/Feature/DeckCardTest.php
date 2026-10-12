<?php

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

/** Renders a deck component with the given props, e.g. renderDeckComponent('<x-deck.card :deck="$deck" />', $deck). */
function renderDeckComponent(string $template, Deck $deck, array $data = []): string
{
    return Blade::render($template, ['deck' => $deck] + $data);
}

test('other users get a like toggle that works as a plain form', function () {
    $deck = Deck::factory()->public()->create(['name' => 'Burn']);
    $this->actingAs(User::factory()->create());

    $html = renderDeckComponent('<x-deck.like-button :deck="$deck" :liked="false" :count="3" />', $deck);

    expect($html)
        ->toContain('action="'.route('decks.like', $deck).'"')
        ->toContain('name="_method" value="POST"')
        ->toContain('aria-pressed="false"')
        ->toContain('likeButton(')
        ->toContain('Like Burn');

    expect(renderDeckComponent('<x-deck.like-button :deck="$deck" :liked="true" :count="3" />', $deck))
        ->toContain('name="_method" value="DELETE"')
        ->toContain('aria-pressed="true"')
        ->toContain('Unlike Burn');
});

test('owners see their like count without a button', function () {
    $deck = Deck::factory()->public()->create();
    $this->actingAs($deck->user);

    expect(renderDeckComponent('<x-deck.like-button :deck="$deck" :liked="false" :count="3" />', $deck))
        ->toContain('data-like="own"')->not->toContain('<form');

    expect(renderDeckComponent('<x-deck.like-button :deck="$deck" :liked="false" :count="3" size="lg" />', $deck))
        ->toContain('3 players liked this deck');
    expect(renderDeckComponent('<x-deck.like-button :deck="$deck" :liked="false" :count="1" size="lg" />', $deck))
        ->toContain('1 player liked this deck');
});

test('guests are pointed to the login page', function () {
    $deck = Deck::factory()->public()->create();

    expect(renderDeckComponent('<x-deck.like-button :deck="$deck" :liked="false" :count="5" size="lg" />', $deck))
        ->toContain('href="'.route('login').'"')
        ->toContain('Log in to like')
        ->not->toContain('<form');
});

test('a public deck card links the deck and its author, with format, count and likes', function () {
    $deck = Deck::factory()->public()->create(['name' => 'Burn', 'game' => 'yugioh', 'format' => 'tcg']);
    $deck->card_count = 40;
    $deck->likes_count = 7;

    $html = renderDeckComponent('<x-deck.card :deck="$deck" />', $deck);

    expect($html)
        ->toContain('href="'.route('decks.show', $deck).'"')
        ->toContain('href="'.route('users.show', $deck->user).'"')
        ->toContain('Yu-Gi-Oh! deck')
        ->toContain('TCG Advanced')
        ->toContain('>40</b>')
        ->toContain('Log in to like')
        ->not->toContain(route('decks.edit', $deck));
});

test('the trending chip only shows in trending order with recent likes', function () {
    $deck = Deck::factory()->public()->create();
    $deck->recent_likes_count = 2;

    expect(renderDeckComponent('<x-deck.card :deck="$deck" trending />', $deck))->toContain('+2 this week');
    expect(renderDeckComponent('<x-deck.card :deck="$deck" />', $deck))->not->toContain('this week');

    $deck->recent_likes_count = 0;
    expect(renderDeckComponent('<x-deck.card :deck="$deck" trending />', $deck))->not->toContain('this week');
});

test('on a profile the card leaves out the author', function () {
    $deck = Deck::factory()->public()->create();

    expect(renderDeckComponent('<x-deck.card :deck="$deck" variant="profile" />', $deck))
        ->toContain('View deck')
        ->not->toContain(route('users.show', $deck->user));
});

test('the owner card shows visibility and the edit and delete actions', function () {
    $deck = Deck::factory()->create(['name' => 'Secret', 'is_public' => false]);
    $this->actingAs($deck->user);

    $html = renderDeckComponent('<x-deck.card :deck="$deck" variant="owner" />', $deck);

    expect($html)
        ->toContain('Private')
        ->toContain('href="'.route('decks.edit', $deck).'"')
        ->toContain('action="'.route('decks.destroy', $deck).'"')
        ->toContain('data-confirm-title="Delete this deck?"')
        ->not->toContain('data-like=');

    $deck->is_public = true;
    $deck->likes_count = 4;

    expect(renderDeckComponent('<x-deck.card :deck="$deck" variant="owner" />', $deck))
        ->toContain('Public')
        ->toContain('data-like="own"');
});

test('an avatar shows the picture, or the initial when there is none', function () {
    $withPicture = User::factory()->make(['username' => 'émilie', 'avatar' => 'avatars/emilie.png']);
    $without = User::factory()->make(['username' => 'émilie']);

    expect($without->initial)->toBe('É')
        ->and(Blade::render('<x-avatar :user="$user" class="h-7 w-7" />', ['user' => $withPicture]))
        ->toContain('<img')->toContain('avatars/emilie.png')->toContain('h-7 w-7')
        ->and(Blade::render('<x-avatar :user="$user" class="h-7 w-7" />', ['user' => $without]))
        ->toContain('>É</span>')->toContain('bg-accent')->not->toContain('<img');
});

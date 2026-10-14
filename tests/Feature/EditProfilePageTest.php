<?php

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function editProfilePage(User $user): string
{
    return test()->actingAs($user)->get(route('profile.edit'))->assertOk()->getContent();
}

test('guests are sent to log in', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
});

test('the card shows who you are, your deck counts and links to your public profile and decks', function () {
    $user = User::factory()->create(['username' => 'kaiba', 'email' => 'kaiba@example.com', 'about' => 'Blue-Eyes fan.']);
    Deck::factory()->for($user)->public()->create();
    Deck::factory()->for($user)->count(2)->create();

    expect(editProfilePage($user))
        ->toContain('<title>Edit profile')
        ->toContain('<section aria-label="Your profile"')
        ->toContain('kaiba</h1>')
        ->toContain('kaiba@example.com')
        ->toContain('Blue-Eyes fan.')
        ->toMatch('/>decks<\/dt>\s*<dd[^>]*>3<\/dd>/')
        ->toMatch('/>public<\/dt>\s*<dd[^>]*>1<\/dd>/')
        ->toMatch('/>private<\/dt>\s*<dd[^>]*>2<\/dd>/')
        ->toContain('href="'.route('users.show', $user).'" class="btn-secondary w-full">View your public profile</a>')
        ->not->toContain('class="btn-secondary w-full">Settings</a>')
        ->toContain('href="'.route('decks').'" class="btn-secondary w-full">View my decks</a>')
        ->not->toContain('id="change-password"')
        ->not->toContain('id="delete-account"');
});

test('without an about text or decks the card says so', function () {
    expect(editProfilePage(User::factory()->create(['about' => null])))
        ->toContain('No about text yet.')
        ->toMatch('/>decks<\/dt>\s*<dd[^>]*>0<\/dd>/');
});

test('one deck reads as deck', function () {
    $user = User::factory()->create();
    Deck::factory()->for($user)->create();

    expect(editProfilePage($user))->toMatch('/>deck<\/dt>\s*<dd[^>]*>1<\/dd>/');
});

test('the edit profile form is filled in, with the picture field and the counter', function () {
    $user = User::factory()->create(['username' => 'kaiba', 'about' => 'Blue-Eyes fan.']);

    expect(editProfilePage($user))
        ->toContain('<section id="edit-profile"')
        ->toContain('<h2 id="edit-profile-title"')
        ->toContain('action="'.route('profile.update').'" enctype="multipart/form-data"')
        ->toContain('value="kaiba"')
        ->toContain('>Blue-Eyes fan.</textarea>')
        ->toContain('14 / 500')
        ->toContain('accept="image/png,image/jpeg,image/webp"')
        ->toContain('JPG, PNG or WebP, max 2 MB')
        ->toContain('href="'.route('users.show', $user).'" class="btn-secondary">Cancel</a>')
        ->not->toContain('type="reset"');
});

test('saving comes back to the form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), ['username' => 'renamed'])
        ->assertRedirect(route('profile.edit').'#edit-profile');
});

test('a failed profile save shows the error and keeps what you typed', function () {
    User::factory()->create(['username' => 'joey']);
    $user = User::factory()->create(['username' => 'kaiba', 'about' => 'Old.']);

    $html = $this->actingAs($user)->from(route('profile.edit'))->followingRedirects()
        ->put(route('profile.update'), ['username' => 'joey', 'about' => 'New text'])
        ->getContent();

    expect($html)->toContain('<p id="username-error" role="alert"')
        ->toContain('>New text</textarea>')
        ->toContain('8 / 500');
});

test('your decks are listed with their series and visibility, newest first', function () {
    $user = User::factory()->create();
    $old = Deck::factory()->for($user)->public()->create(['name' => 'Old Dragons', 'game' => 'yugioh', 'created_at' => now()->subDay()]);
    $new = Deck::factory()->for($user)->create(['name' => 'New Spells', 'game' => 'magic']);
    Deck::factory()->create(['name' => 'Not Mine']);

    $html = editProfilePage($user);

    expect($html)
        ->toContain('<section id="your-decks" aria-labelledby="your-decks-title"')
        ->toContain('action="'.route('decks.bulk').'"')
        ->toContain('<input type="checkbox" name="decks[]" value="'.$old->id.'" id="deck-'.$old->id.'"')
        ->toContain('<label for="deck-'.$new->id.'"')
        ->toMatch('/Old Dragons<\/span>\s*<span[^>]*>'.config('series.yugioh.label').'<\/span>\s*<span class="visibility-badge"\s+data-public\s*>Public<\/span>/')
        ->toMatch('/New Spells<\/span>\s*<span[^>]*>'.config('series.magic.label').'<\/span>\s*<span class="visibility-badge"\s*>Private<\/span>/')
        ->toContain('href="'.route('decks.show', $old).'"')
        ->toContain('aria-label="Open Old Dragons"')
        ->not->toContain('Not Mine')
        ->and(strpos($html, 'New Spells'))->toBeLessThan(strpos($html, 'Old Dragons'));
});

test('the deck form has make public, make private and a delete that asks first', function () {
    $user = User::factory()->create();
    Deck::factory()->for($user)->create();

    expect(editProfilePage($user))
        ->toContain('name="action" value="public"')
        ->toContain('>Make public</button>')
        ->toContain('name="action" value="private"')
        ->toContain('>Make private</button>')
        ->toContain('name="action" value="delete"')
        ->toContain('data-confirm-title="Delete the selected decks?"')
        ->toContain('data-confirm-button="Delete decks"')
        ->toContain('>Delete selected</button>');
});

test('without decks the panel points to my decks and has no form', function () {
    expect(editProfilePage(User::factory()->create()))
        ->toContain('<section id="your-decks"')
        ->toContain('No decks yet')
        ->toContain('href="'.route('decks').'" class="btn-primary">Go to my decks</a>')
        ->not->toContain('action="'.route('decks.bulk').'"');
});

test('selecting nothing shows the error under the list', function () {
    $user = User::factory()->create();
    Deck::factory()->for($user)->create();

    $html = $this->actingAs($user)->from(route('profile.edit'))->followingRedirects()
        ->post(route('decks.bulk'), ['action' => 'public'])
        ->getContent();

    expect($html)->toContain('<p id="decks-error" role="alert"')
        ->toContain('Select at least one deck.');
});

test('the page script previews the picture, counts the about text and handles the deck selection', function () {
    $user = User::factory()->create(['about' => 'Blue-Eyes fan.']);
    Deck::factory()->for($user)->count(2)->create();

    expect(editProfilePage($user))
        ->toContain('x-data="profileForm(14)"')
        ->toContain('<img x-show="preview" x-cloak :src="preview" alt=""')
        ->toContain('@change="pick($event)"')
        ->toContain('x-text="counter">14 / 500</span>')
        ->toContain('@input="count($event)"')
        ->toContain('x-data="deckSelection(2)" @change="sync($el)"')
        ->toContain(':checked="allSelected" @change.stop="toggleAll($event)"')
        ->toContain('aria-live="polite" x-text="summary"')
        ->toContain(':disabled="! count"');
});

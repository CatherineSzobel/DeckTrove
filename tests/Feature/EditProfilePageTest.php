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

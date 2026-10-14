<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function settingsPage(User $user): string
{
    return test()->actingAs($user)->get(route('settings'))->assertOk()->getContent();
}

test('guests are sent to log in', function () {
    $this->get(route('settings'))->assertRedirect(route('login'));
});

test('profile takes you to your public profile', function () {
    $user = User::factory()->create(['username' => 'kaiba']);

    $this->actingAs($user)->get(route('profile'))->assertRedirect(route('users.show', $user));
});

test('the settings page has its own heading and no profile card or edit form', function () {
    expect(settingsPage(User::factory()->create()))
        ->toContain('<title>Settings')
        ->toContain('<h1 class="heading-display text-[clamp(40px,5vw,56px)] text-ink">Settings</h1>')
        ->not->toContain('aria-label="Your profile"')
        ->not->toContain('id="edit-profile"');
});

test('change password has its own fields and comes back to its panel', function () {
    $user = User::factory()->create();

    expect(settingsPage($user))
        ->toContain('<section id="change-password"')
        ->toContain('id="current_password" name="current_password"')
        ->toContain('id="new_password" name="password"')
        ->toContain('id="new_password_confirmation" name="password_confirmation"');

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'password',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertRedirect(route('settings').'#change-password');
});

test('a wrong current password shows only under change password', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->from(route('settings'))->followingRedirects()
        ->put(route('profile.password'), ['current_password' => 'wrong', 'password' => 'brand-new-password', 'password_confirmation' => 'brand-new-password'])
        ->getContent();

    expect($html)->toContain('<p id="current_password-error" role="alert"')
        ->not->toContain('id="delete_password-error"');
});

test('a wrong password shows only under delete account, which asks to confirm', function () {
    $user = User::factory()->create();

    $html = $this->actingAs($user)->from(route('settings'))->followingRedirects()
        ->delete(route('profile.destroy'), ['password' => 'wrong'])
        ->getContent();

    expect($html)->toContain('<section id="delete-account"')
        ->toContain('<p id="delete_password-error" role="alert"')
        ->not->toContain('id="new_password-error"')
        ->not->toContain('id="current_password-error"')
        ->toContain('data-confirm-title="Delete your account?"')
        ->toContain('class="btn-danger self-start">Delete my account</button>');
});

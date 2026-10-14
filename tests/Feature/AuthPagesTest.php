<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('the log in page has its card, fields, links and showcase', function () {
    expect($this->get(route('login'))->assertOk()->getContent())
        ->toContain('<p class="eyebrow">Welcome back</p>')
        ->toContain('>Log in</h1>')
        ->toContain('Log in to your DeckTrove account.')
        ->toContain('autocomplete="username"')
        ->toContain('autocomplete="current-password"')
        ->toContain('name="remember" value="1"')
        ->toContain('Keep me logged in')
        ->toContain('href="'.route('password.request').'"')
        ->toContain('href="'.route('register').'"')
        ->toContain('aria-label="About DeckTrove"')
        ->toContain('Build with rules checked as you go')
        ->toContain('alt="DeckTrove card database"')
        ->toContain('alt="DeckTrove deck builder"')
        ->not->toContain('carousel');
});

test('the forgot password link is a full-size tap target', function () {
    expect($this->get(route('login'))->getContent())
        ->toContain('class="focus-ring inline-flex min-h-11 items-center rounded-sm text-sm font-bold text-accent-ink">Forgot your password?</a>');
});

test('a failed log in shows the error under the username and keeps it', function () {
    User::factory()->create(['username' => 'kaiba']);

    $html = $this->from(route('login'))->followingRedirects()
        ->post(route('login'), ['username' => 'kaiba', 'password' => 'wrong'])
        ->getContent();

    expect($html)->toContain('aria-invalid="true" aria-describedby="username-error"')
        ->toContain('<p id="username-error" role="alert"')
        ->toContain('Your provided credentials do not match our records.')
        ->toContain('value="kaiba"')
        ->toContain('name="password" type="password" value=""');
});

test('keep me logged in sets the remember cookie', function () {
    $user = User::factory()->create();

    $this->post(route('login'), ['username' => $user->username, 'password' => 'password', 'remember' => '1'])
        ->assertCookie(Auth::guard()->getRecallerName());
});

test('without keep me logged in there is no remember cookie', function () {
    $user = User::factory()->create();

    $this->post(route('login'), ['username' => $user->username, 'password' => 'password'])
        ->assertCookieMissing(Auth::guard()->getRecallerName());
});

test('the create account page has its card, fields, link and showcase', function () {
    $html = $this->get(route('register'))->assertOk()->getContent();

    expect($html)->toContain('<title>Create account · DeckTrove</title>')
        ->toContain('<p class="eyebrow">Join DeckTrove</p>')
        ->toContain('>Create account</h1>')
        ->toContain('autocomplete="email"')
        ->toContain('name="password_confirmation"')
        ->toContain('Pick up ideas from other players’ builds')
        ->toContain('href="'.route('login').'"')
        ->and(substr_count($html, 'autocomplete="new-password"'))->toBe(2);
});

test('create account errors show under their fields', function () {
    $html = $this->from(route('register'))->followingRedirects()->post(route('register'), [
        'username' => 'newduelist',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'other',
    ])->getContent();

    expect($html)->toContain('<p id="email-error" role="alert"')
        ->toContain('<p id="password-error" role="alert"')
        ->toContain('value="newduelist"');
});

test('the forgot password page is a single card with a way back', function () {
    expect($this->get(route('password.request'))->assertOk()->getContent())
        ->toContain('<p class="eyebrow">Account help</p>')
        ->toContain('>Forgot your password?</h1>')
        ->toContain('Enter your account’s email and we’ll send you a link to choose a new one.')
        ->toContain('autocomplete="email"')
        ->toContain('Send reset link')
        ->toContain('href="'.route('login').'" class="focus-ring rounded-sm font-bold text-accent-ink">Back to log in</a>')
        ->not->toContain('aria-label="About DeckTrove"');
});

test('the reset page carries the token and the e-mail, escaped', function () {
    expect($this->get(route('password.reset', ['token' => 'abc123', 'email' => '"><b>x</b>']))->assertOk()->getContent())
        ->toContain('>Choose a new password</h1>')
        ->toContain('<input type="hidden" name="token" value="abc123">')
        ->toContain('value="&quot;&gt;&lt;b&gt;x&lt;/b&gt;"')
        ->toContain('minlength="8"')
        ->toContain('name="password_confirmation"')
        ->not->toContain('aria-label="About DeckTrove"');
});

test('a rejected reset shows the error under the e-mail', function () {
    $user = User::factory()->create();

    $html = $this->from(route('password.reset', ['token' => 'nope', 'email' => $user->email]))->followingRedirects()
        ->post(route('password.store'), ['token' => 'nope', 'email' => $user->email, 'password' => 'brand-new-password', 'password_confirmation' => 'brand-new-password'])
        ->getContent();

    expect($html)->toContain('<p id="email-error" role="alert"');
});

test('a reset link with an array e-mail still opens', function () {
    $this->get(route('password.reset', ['token' => 'abc123', 'email' => ['a']]))->assertOk();
});

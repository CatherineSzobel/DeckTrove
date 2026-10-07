<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users can register and are logged in', function () {
    $this->post('/register', [
        'username' => 'newduelist',
        'email' => 'new@example.com',
        'password' => 'correct-horse-battery',
        'password_confirmation' => 'correct-horse-battery',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    expect(User::where('username', 'newduelist')->first()->password)->not->toBe('correct-horse-battery');
});

test('users can log in and are sent to the page they originally wanted', function () {
    $user = User::factory()->create();

    $this->get(route('decks'))->assertRedirect(route('login'));

    $this->post('/login', ['username' => $user->username, 'password' => 'password'])
        ->assertRedirect(route('decks'));

    $this->assertAuthenticatedAs($user);
});

test('login is rate limited', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['username' => $user->username, 'password' => 'wrong']);
    }

    $this->post('/login', ['username' => $user->username, 'password' => 'password'])
        ->assertStatus(429);

    $this->assertGuest();
});

test('logging out invalidates the session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('index'));

    $this->assertGuest();
});

test('profile can be updated without an avatar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.update'), ['username' => 'renamed', 'about' => 'Hi'])
        ->assertRedirect(route('profile'));

    expect($user->fresh())->username->toBe('renamed')->about->toBe('Hi');
});

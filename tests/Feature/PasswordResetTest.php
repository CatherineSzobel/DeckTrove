<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('the login page links to password reset', function () {
    $this->get(route('login'))->assertOk()->assertSee(route('password.request'));
});

test('a reset link is emailed to existing users', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->get(route('password.request'))->assertOk();
    $this->post(route('password.email'), ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHas('success');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('the response does not reveal whether an email has an account', function () {
    Notification::fake();

    $this->post(route('password.email'), ['email' => 'nobody@example.com'])
        ->assertRedirect()
        ->assertSessionHas('success')
        ->assertSessionHasNoErrors();

    Notification::assertNothingSent();
});

test('users can choose a new password with a valid reset link', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertOk()
        ->assertSee($user->email);

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertRedirect(route('login'))->assertSessionHas('success');

    expect(Hash::check('brand-new-password', $user->fresh()->password))->toBeTrue();

    // The link only works once.
    $this->post(route('password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'another-password',
        'password_confirmation' => 'another-password',
    ])->assertSessionHasErrors('email');
});

test('an invalid reset token is rejected', function () {
    $user = User::factory()->create();

    $this->post(route('password.store'), [
        'token' => 'not-a-real-token',
        'email' => $user->email,
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('reset link requests are rate limited', function () {
    Notification::fake();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('password.email'), ['email' => "user$attempt@example.com"]);
    }

    $this->post(route('password.email'), ['email' => 'user6@example.com'])->assertStatus(429);
});

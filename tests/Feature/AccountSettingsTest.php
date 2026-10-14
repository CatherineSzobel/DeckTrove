<?php

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('the edit profile page has password and delete account sections', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('settings'))
        ->assertOk()
        ->assertSee(route('profile.password'))
        ->assertSee(route('profile.destroy'));
});

test('users can change their password with their current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'password',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertRedirect(route('settings').'#change-password')->assertSessionHas('success');

    expect(Hash::check('brand-new-password', $user->fresh()->password))->toBeTrue();
});

// Auth::forgetGuards() between requests stands in for a new request: the test app otherwise keeps the logged-in
// user (and its old password hash) in memory.

test('a password change signs out sessions on other devices', function () {
    $user = User::factory()->create();

    $this->post(route('login'), ['username' => $user->username, 'password' => 'password']);
    $this->get(route('dashboard'))->assertOk();

    $user->update(['password' => 'brand-new-password']);
    Auth::forgetGuards();

    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('a password change stops keep me logged in on other devices', function () {
    $user = User::factory()->create();
    $recaller = $this->post(route('login'), ['username' => $user->username, 'password' => 'password', 'remember' => '1'])
        ->getCookie(Auth::guard()->getRecallerName());

    $this->flushSession();
    Auth::forgetGuards();
    $this->withCookie($recaller->getName(), $recaller->getValue())->get(route('dashboard'))->assertOk();

    $user->update(['password' => 'brand-new-password']);
    $this->flushSession();
    Auth::forgetGuards();

    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('the device that changes the password stays logged in, keep me logged in included', function () {
    $user = User::factory()->create();
    $recaller = $this->post(route('login'), ['username' => $user->username, 'password' => 'password', 'remember' => '1'])
        ->getCookie(Auth::guard()->getRecallerName());
    Auth::forgetGuards();

    $renewed = $this->withCookie($recaller->getName(), $recaller->getValue())->put(route('profile.password'), [
        'current_password' => 'password',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertRedirect(route('settings').'#change-password')->getCookie(Auth::guard()->getRecallerName());
    Auth::forgetGuards();

    $this->get(route('dashboard'))->assertOk();

    $this->flushSession();
    Auth::forgetGuards();
    expect($renewed)->not->toBeNull();
    $this->withCookie($renewed->getName(), $renewed->getValue())->get(route('dashboard'))->assertOk();
});

test('changing the password requires the correct current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'wrong-password',
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ])->assertSessionHasErrorsIn('updatePassword', 'current_password');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('users can delete their account, decks and avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $this->actingAs($user)->put(route('profile.update'), [
        'username' => $user->username,
        'avatar' => UploadedFile::fake()->image('me.png'),
    ]);
    $avatar = $user->fresh()->avatar;
    $deck = Deck::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('index'));

    $this->assertGuest();
    expect(User::find($user->id))->toBeNull();
    expect(Deck::find($deck->id))->toBeNull();
    Storage::disk('public')->assertMissing($avatar);
});

test('deleting an account requires the correct password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'wrong-password'])
        ->assertSessionHasErrorsIn('deleteAccount', 'password');

    expect(User::find($user->id))->not->toBeNull();
    $this->assertAuthenticatedAs($user);
});

test('guests cannot change passwords or delete accounts', function () {
    $this->put(route('profile.password'))->assertRedirect(route('login'));
    $this->delete(route('profile.destroy'))->assertRedirect(route('login'));
});

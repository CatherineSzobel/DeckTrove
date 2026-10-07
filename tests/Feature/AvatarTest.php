<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('avatars are stored on the configured media disk', function () {
    // On Laravel Cloud, MEDIA_DISK points at the attached bucket's disk.
    config(['filesystems.media' => 'r2']);
    Storage::fake('r2');
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'username' => $user->username,
        'avatar' => UploadedFile::fake()->image('me.png'),
    ])->assertRedirect(route('profile'));

    $path = $user->fresh()->avatar;
    Storage::disk('r2')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
    expect($user->fresh()->avatarUrl())->toBe(Storage::disk('r2')->url($path));
});

test('replacing an avatar deletes the old file', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), ['username' => $user->username, 'avatar' => UploadedFile::fake()->image('a.png')]);
    $old = $user->fresh()->avatar;

    $this->actingAs($user)->put(route('profile.update'), ['username' => $user->username, 'avatar' => UploadedFile::fake()->image('b.png')]);

    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($user->fresh()->avatar);
});

test('users without an avatar have no avatar url', function () {
    expect(User::factory()->create()->avatarUrl())->toBeNull();
});

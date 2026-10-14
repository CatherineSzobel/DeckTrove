<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

test('the profile card has a label, the banner, the avatar and the slot', function () {
    $user = User::factory()->create(['username' => 'kaiba']);

    $html = Blade::render('<x-profile.card :user="$user" label="Your profile" class="lg:sticky"><h1>Name here</h1></x-profile.card>', ['user' => $user]);

    expect($html)->toContain('<section aria-label="Your profile" class="min-w-0 overflow-hidden rounded-3xl border border-line bg-surface lg:sticky">')
        ->toContain('<div class="profile-banner" aria-hidden="true"><span></span><span></span><span></span></div>')
        ->toContain('>K</span>')
        ->toContain('<h1>Name here</h1>');
});

test('the card is labelled Profile by default and shows the picture when there is one', function () {
    $user = User::factory()->create(['avatar' => 'avatars/kaiba.png']);

    expect(Blade::render('<x-profile.card :user="$user" />', ['user' => $user]))
        ->toContain('<section aria-label="Profile"')
        ->toContain('avatars/kaiba.png');
});

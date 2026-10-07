<?php

use App\Models\YugiohCard;
use App\Services\YugiohService;
use App\ViewModels\CardViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('public'));

function fakeImages(): void
{
    Http::fake(['images.ygoprodeck.com/*' => Http::response('fake-jpeg-bytes', 200, ['Content-Type' => 'image/jpeg'])]);
}

function viewModel(YugiohCard $card): CardViewModel
{
    return new CardViewModel(app(YugiohService::class)->find((string) $card->id), config('series.yugioh'));
}

test('card images are downloaded to the media disk once', function () {
    fakeImages();
    $card = YugiohCard::factory()->create();

    $this->artisan('yugioh:images', ['--delay' => 0])->assertSuccessful();

    Storage::disk('public')->assertExists("yugioh/cards/{$card->id}.jpg");
    Storage::disk('public')->assertExists("yugioh/cards_small/{$card->id}.jpg");
    expect($card->fresh()->images_hosted_at)->not->toBeNull();
    Http::assertSentCount(2);

    // Running it again skips cards that are already hosted.
    $this->artisan('yugioh:images', ['--delay' => 0])->assertSuccessful();
    Http::assertSentCount(2);
});

test('hosted cards link to our storage, others fall back to YGOPRODeck', function () {
    $hosted = YugiohCard::factory()->create(['images_hosted_at' => now()]);
    $notHosted = YugiohCard::factory()->create();

    expect(viewModel($hosted)->image())->toBe(Storage::disk('public')->url("yugioh/cards/{$hosted->id}.jpg"));
    expect(viewModel($hosted)->imageSmall())->toBe(Storage::disk('public')->url("yugioh/cards_small/{$hosted->id}.jpg"));
    expect(viewModel($notHosted)->image())->toStartWith('https://images.ygoprodeck.com/');
});

test('the limit option downloads only some cards', function () {
    fakeImages();
    YugiohCard::factory()->count(3)->create();

    $this->artisan('yugioh:images', ['--delay' => 0, '--limit' => 2])->assertSuccessful();

    expect(YugiohCard::whereNotNull('images_hosted_at')->count())->toBe(2);
});

test('the download stops as soon as YGOPRODeck rate limits us', function () {
    Http::fake(['images.ygoprodeck.com/*' => Http::response('', 429)]);
    YugiohCard::factory()->count(3)->create();

    $this->artisan('yugioh:images', ['--delay' => 0])->assertFailed();

    Http::assertSentCount(1);
});

test('failed downloads are left for the next run', function () {
    Http::fake(['images.ygoprodeck.com/*' => Http::response('', 404)]);
    $card = YugiohCard::factory()->create();

    $this->artisan('yugioh:images', ['--delay' => 0])->assertSuccessful();

    expect($card->fresh()->images_hosted_at)->toBeNull();
    Storage::disk('public')->assertMissing("yugioh/cards/{$card->id}.jpg");
});

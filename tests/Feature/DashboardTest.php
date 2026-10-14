<?php

use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

// Not in beforeEach: a second Http::fake can't override the first, and one test needs Scryfall to fail.
function fakeRandomMagicCard(): void
{
    Http::fake(['api.scryfall.com/cards/random' => Http::response(['id' => '00000000-0000-0000-0000-000000000001', 'name' => 'Random Magic Card'])]);
}

function dashboardUser(): User
{
    return User::factory()->create(['username' => 'duelist']);
}

/** The series switch link that is marked current; Blade's inline @if leaves extra spaces, hence the regex. */
function currentSegment(string $label): string
{
    return '/aria-current="page"\s+class="segment">'.preg_quote($label, '/').'<\/a>/';
}

test('guests are sent to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('the header welcomes the user and links to a new deck, the profile and settings', function () {
    fakeRandomMagicCard();

    $this->actingAs(dashboardUser())->withSession(['series' => 'magic'])
        ->get(route('dashboard'))->assertOk()
        ->assertSee('Welcome back, duelist')
        ->assertSee('href="'.route('decks.builder', 'magic').'" class="btn-primary"', false)
        ->assertSee('href="'.route('profile').'" class="btn-secondary">Profile</a>', false)
        ->assertSee('href="'.route('settings').'" class="btn-secondary">Settings</a>', false);
});

test('without a series in the session, New deck and the random cards use the first series', function () {
    YugiohCard::factory()->fromCard(['name' => 'Dark Magician'])->create();

    $this->actingAs(dashboardUser())->get(route('dashboard'))->assertOk()
        ->assertSee('href="'.route('decks.builder', 'yugioh').'" class="btn-primary"', false)
        ->assertSee('alt="Dark Magician"', false);
});

test('the stat tiles show the deck counts', function () {
    $user = dashboardUser();
    Deck::factory()->for($user)->count(2)->create(['game' => 'yugioh']);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertSeeInOrder(['Total decks', '2', 'Yu-Gi-Oh!', '2', 'Magic: The Gathering', '0']);
});

test('the series switch marks All by default, the chosen series, and All for an unknown one', function () {
    $this->actingAs(dashboardUser());

    expect($this->get(route('dashboard'))->getContent())->toMatch(currentSegment('All'));
    expect($this->get(route('dashboard', ['tcg' => 'magic']))->getContent())
        ->toMatch(currentSegment('Magic: The Gathering'))->not->toMatch(currentSegment('All'));
    expect($this->get(route('dashboard', ['tcg' => 'pokemon']))->assertOk()->getContent())->toMatch(currentSegment('All'));
});

test('your decks shows the three newest as tiles, filtered by series, with View all', function () {
    $user = dashboardUser();
    foreach (['Oldest', 'Older', 'Newer', 'Newest'] as $age => $name) {
        Deck::factory()->for($user)->create(['name' => $name, 'game' => $age === 3 ? 'magic' : 'yugioh', 'created_at' => now()->subDays(10 - $age)]);
    }

    $this->actingAs($user)->get(route('dashboard'))
        ->assertSeeInOrder(['Newest', 'Newer', 'Older'])->assertDontSee('Oldest')
        ->assertSee('href="'.route('decks').'"', false)
        ->assertSee('data-size="sm"', false);

    $this->get(route('dashboard', ['tcg' => 'magic']))->assertSee('Newest')->assertDontSee('Newer');
});

test('an account without decks gets the empty state with its actions', function () {
    $this->actingAs(dashboardUser())->withSession(['series' => 'yugioh'])->get(route('dashboard'))
        ->assertSee('You don’t have any decks yet')
        ->assertSee('href="'.route('decks.builder', 'yugioh').'"', false)
        ->assertSee('Browse public decks');
});

test('a series filter without decks says so', function () {
    $user = dashboardUser();
    Deck::factory()->for($user)->create(['game' => 'yugioh']);

    $this->actingAs($user)->get(route('dashboard', ['tcg' => 'magic']))
        ->assertSee('No Magic: The Gathering decks yet')->assertSee('Browse public decks');
});

test('the quick links come from the service', function () {
    $user = dashboardUser();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertViewHas('quickLinks', app(DashboardService::class)->quickLinks($user))
        ->assertSee('Your public profile')
        ->assertSee('href="'.route('users.show', $user).'"', false);
});

test('random cards come from the session series and open the card page in the same tab', function () {
    fakeRandomMagicCard();
    YugiohCard::factory()->fromCard(['name' => 'Dark Magician'])->create();

    $this->actingAs(dashboardUser())->withSession(['series' => 'magic'])->get(route('dashboard'))
        ->assertSee('Discover random cards')
        ->assertSee('href="'.url('/magic/card/00000000-0000-0000-0000-000000000001').'" class="random-card"', false)
        ->assertDontSee('alt="Dark Magician"', false)
        ->assertDontSee('moving-carousel');
});

test('without random cards the section keeps its switch and says so', function () {
    Http::fake(['api.scryfall.com/cards/random' => Http::response([], 500)]);

    // The switch stays, so a series that has no cards right now can't trap the user.
    $this->actingAs(dashboardUser())->withSession(['series' => 'magic'])->get(route('dashboard'))
        ->assertOk()->assertSee('Discover random cards')->assertSee('No random cards right now.')
        ->assertSee('aria-label="Random cards series"', false)->assertDontSee('class="random-card"', false);
});

test('a long username wraps instead of widening the page on phones', function () {
    $html = $this->actingAs(User::factory()->create(['username' => str_repeat('w', 30)]))->get(route('dashboard'))->getContent();

    expect($html)->toMatch('/<div class="[^"]*min-w-0[^"]*">\s*<p class="eyebrow">Dashboard<\/p>\s*<h1 class="[^"]*break-words[^"]*">Welcome back, w{30}<\/h1>/');
});

test('the random cards switch picks the series and remembers it', function () {
    fakeRandomMagicCard();
    YugiohCard::factory()->fromCard(['name' => 'Dark Magician'])->create();

    $response = $this->actingAs(dashboardUser())->withSession(['series' => 'yugioh'])->get(route('dashboard', ['cards' => 'magic']));

    $response->assertOk()->assertSessionHas('series', 'magic')
        ->assertSee('class="random-card"', false)->assertDontSee('alt="Dark Magician"', false)
        ->assertSee('href="'.route('decks.builder', 'magic').'" class="btn-primary"', false);
    expect($response->getContent())->toMatch('/<div role="group" aria-label="Random cards series"[^>]*>.*?'.trim(currentSegment('Magic: The Gathering'), '/').'/s');
});

test('an unknown random cards series falls back to the session series', function () {
    YugiohCard::factory()->fromCard(['name' => 'Dark Magician'])->create();

    $this->actingAs(dashboardUser())->withSession(['series' => 'yugioh'])->get(route('dashboard', ['cards' => 'pokemon']))
        ->assertOk()->assertSessionHas('series', 'yugioh')->assertSee('alt="Dark Magician"', false);
});

<?php

use App\Decks\PublicDeckQuery;
use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

/** The names of the decks on the page, in order. */
function publicDeckNames(TestResponse $response): array
{
    return collect($response->viewData('decks')->items())->pluck('name')->all();
}

const DEFAULT_FILTERS = ['q' => null, 'sort' => 'newest', 'game' => null, 'format' => null, 'liked' => false];

test('newest is the default order and private decks are never listed', function () {
    Deck::factory()->public()->create(['name' => 'Old', 'created_at' => now()->subDays(3)]);
    Deck::factory()->public()->create(['name' => 'New', 'created_at' => now()->subDay()]);
    Deck::factory()->create(['name' => 'Private']);

    expect(publicDeckNames($this->get(route('decks.index'))))->toBe(['New', 'Old']);
});

test('most liked sorts by all-time likes, then newest', function () {
    $few = Deck::factory()->public()->create(['name' => 'Few', 'created_at' => now()->subDays(5)]);
    $many = Deck::factory()->public()->create(['name' => 'Many', 'created_at' => now()->subDays(9)]);
    Deck::factory()->public()->create(['name' => 'None', 'created_at' => now()->subDays(1)]);
    DeckLike::factory()->for($few)->create();
    DeckLike::factory()->count(3)->for($many)->create();

    expect(publicDeckNames($this->get(route('decks.index', ['sort' => 'liked']))))->toBe(['Many', 'Few', 'None']);
});

test('trending sorts by likes in the last 7 days, then all-time likes, then newest', function () {
    $favourite = Deck::factory()->public()->create(['name' => 'Favourite', 'created_at' => now()->subDays(30)]);
    $hot = Deck::factory()->public()->create(['name' => 'Hot', 'created_at' => now()->subDays(20)]);
    $edge = Deck::factory()->public()->create(['name' => 'Edge', 'created_at' => now()->subDays(25)]);
    Deck::factory()->public()->create(['name' => 'New', 'created_at' => now()->subDay()]);
    DeckLike::factory()->count(5)->for($favourite)->create(['created_at' => now()->subDays(10)]);
    DeckLike::factory()->count(2)->for($hot)->create(['created_at' => now()->subDays(2)]);
    DeckLike::factory()->for($edge)->create(['created_at' => now()->subDays(8)]);

    $response = $this->get(route('decks.index', ['sort' => 'trending']));

    expect(publicDeckNames($response))->toBe(['Hot', 'Favourite', 'Edge', 'New'])
        ->and($response->viewData('decks')->first()->recent_likes_count)->toBe(2);

    // Six days later, Hot's likes are 8 days old and no longer count as recent.
    $this->travel(6)->days();

    expect(publicDeckNames($this->get(route('decks.index', ['sort' => 'trending']))))->toBe(['Favourite', 'Hot', 'Edge', 'New']);
});

test('search matches deck name, description and username, case-insensitively', function () {
    Deck::factory()->public()->create(['name' => 'Dragon Control', 'description' => 'Slow and steady.']);
    Deck::factory()->public()->create(['name' => 'Burn', 'description' => 'Lots of DRAGONS.']);
    Deck::factory()->public()->for(User::factory()->state(['username' => 'dragonlord']))->create(['name' => 'Mill', 'description' => 'Empty the deck.']);
    Deck::factory()->public()->create(['name' => 'Elves', 'description' => 'Go wide.']);
    Deck::factory()->create(['name' => 'Secret Dragon']);

    $response = $this->get(route('decks.index', ['q' => '  dRaGoN ']));

    expect(publicDeckNames($response))->toEqualCanonicalizing(['Dragon Control', 'Burn', 'Mill'])
        ->and($response->viewData('filters')['q'])->toBe('dRaGoN');
});

test('the format filter works within a game, and casual means no format', function () {
    Deck::factory()->public()->create(['name' => 'TCG', 'game' => 'yugioh', 'format' => 'tcg']);
    Deck::factory()->public()->create(['name' => 'OCG', 'game' => 'yugioh', 'format' => 'ocg']);
    Deck::factory()->public()->create(['name' => 'Casual', 'game' => 'yugioh', 'format' => null]);
    Deck::factory()->public()->create(['name' => 'Modern', 'game' => 'magic', 'format' => 'modern']);

    $names = fn (array $params) => publicDeckNames($this->get(route('decks.index', $params)));

    expect($names(['game' => 'yugioh', 'format' => 'tcg']))->toBe(['TCG'])
        ->and($names(['game' => 'yugioh', 'format' => 'casual']))->toBe(['Casual'])
        ->and($names(['format' => 'tcg']))->toHaveCount(4)
        ->and($names(['game' => 'yugioh', 'format' => 'modern']))->toEqualCanonicalizing(['TCG', 'OCG', 'Casual']);
});

test('liked shows only the decks the viewer liked', function () {
    $viewer = User::factory()->create();
    $liked = Deck::factory()->public()->create(['name' => 'Liked']);
    $other = Deck::factory()->public()->create(['name' => 'Liked by someone else']);
    Deck::factory()->public()->create(['name' => 'Not liked']);
    DeckLike::factory()->for($liked)->for($viewer)->create();
    DeckLike::factory()->for($other)->create();

    $response = $this->actingAs($viewer)->get(route('decks.index', ['liked' => 1]));

    expect(publicDeckNames($response))->toBe(['Liked'])
        ->and($response->viewData('filters')['liked'])->toBeTrue();
});

test('liked is ignored for guests', function () {
    Deck::factory()->public()->count(2)->create();

    $response = $this->get(route('decks.index', ['liked' => 1]));

    expect(publicDeckNames($response))->toHaveCount(2)
        ->and($response->viewData('filters')['liked'])->toBeFalse();
});

test('invalid parameters fall back to the defaults', function () {
    Deck::factory()->public()->count(2)->create();

    $this->get(route('decks.index', ['sort' => 'bogus', 'game' => 'pokemon', 'format' => 'tcg', 'q' => '   ']))
        ->assertOk()
        ->assertViewHas('filters', DEFAULT_FILTERS)
        ->assertViewHas('decks', fn ($decks) => $decks->total() === 2);
});

test('array-valued parameters are ignored instead of failing', function () {
    Deck::factory()->public()->create();

    $this->get('/public-deck?game[]=magic&sort[]=liked&q[]=x&format[]=tcg&liked[]=1')
        ->assertOk()
        ->assertViewHas('filters', DEFAULT_FILTERS);
});

test('long searches are cut to 100 characters and wildcard characters do no harm', function () {
    Deck::factory()->public()->create(['name' => '100% Burn']);

    $this->get(route('decks.index', ['q' => str_repeat('a', 300)]))
        ->assertOk()
        ->assertViewHas('filters', fn (array $filters) => $filters['q'] === str_repeat('a', 100));

    $this->get(route('decks.index', ['q' => '%_']))->assertOk();
});

test('page links keep the filters and the sort', function () {
    Deck::factory()->public()->count(19)->create(['game' => 'magic']);

    $response = $this->get(route('decks.index', ['game' => 'magic', 'sort' => 'liked']));
    $next = $response->viewData('decks')->nextPageUrl();

    expect($next)->toContain('game=magic')->toContain('sort=liked')->toContain('page=2');
});

test("the list exposes like counts, the viewer's likes, sorts and formats", function () {
    $viewer = User::factory()->create();
    $deck = Deck::factory()->public()->create(['game' => 'yugioh', 'format' => 'tcg']);
    DeckLike::factory()->for($deck)->for($viewer)->create();
    DeckLike::factory()->for($deck)->create();

    $this->actingAs($viewer)
        ->get(route('decks.index', ['game' => 'yugioh']))
        ->assertViewHas('decks', function ($decks) {
            $deck = $decks->first();

            return (int) $deck->likes_count === 2
                && $deck->liked_by_viewer === true
                && $deck->format_label === 'TCG Advanced';
        })
        ->assertViewHas('sorts', ['newest' => 'Newest', 'liked' => 'Most liked', 'trending' => 'Trending'])
        ->assertViewHas('formats', ['casual' => 'Casual', 'tcg' => 'TCG Advanced', 'ocg' => 'OCG']);
});

test('guests see like counts but no liked state, and no formats without a game', function () {
    DeckLike::factory()->create();

    $this->get(route('decks.index'))
        ->assertViewHas('decks', fn ($decks) => (int) $decks->first()->likes_count === 1 && ! $decks->first()->liked_by_viewer)
        ->assertViewHas('formats', []);
});

test("a deck's format label is Casual without a format, else the config label", function () {
    expect(Deck::factory()->make(['game' => 'yugioh', 'format' => null])->format_label)->toBe('Casual')
        ->and(Deck::factory()->make(['game' => 'yugioh', 'format' => 'tcg'])->format_label)->toBe('TCG Advanced')
        ->and(Deck::factory()->make(['game' => 'magic', 'format' => 'pauper'])->format_label)->toBe('Pauper');
});

/** A PublicDeckQuery for the given query string, as seen by $viewer (a guest when null). */
function publicDeckQuery(array $params, ?User $viewer = null): PublicDeckQuery
{
    $request = Request::create('/public-deck', 'GET', $params);
    $request->setUserResolver(fn () => $viewer);

    return PublicDeckQuery::fromRequest($request);
}

test('queryWith keeps the applied filters, applies changes and drops removed ones and the page', function () {
    $query = publicDeckQuery(['q' => 'dragon', 'sort' => 'liked', 'game' => 'yugioh', 'format' => 'tcg', 'page' => 3]);

    expect($query->queryWith(['sort' => 'trending']))->toBe(['q' => 'dragon', 'sort' => 'trending', 'game' => 'yugioh', 'format' => 'tcg'])
        ->and($query->queryWith(['game' => 'magic', 'format' => null]))->toBe(['q' => 'dragon', 'sort' => 'liked', 'game' => 'magic'])
        ->and(publicDeckQuery(['liked' => 1], User::factory()->create())->queryWith())->toBe(['sort' => 'newest', 'liked' => 1]);
});

test('isFiltering and sortNote describe the applied filters', function () {
    expect(publicDeckQuery(['sort' => 'trending'])->isFiltering())->toBeFalse()
        ->and(publicDeckQuery(['q' => 'burn'])->isFiltering())->toBeTrue()
        ->and(publicDeckQuery(['game' => 'magic'])->isFiltering())->toBeTrue()
        ->and(publicDeckQuery(['sort' => 'trending'])->sortNote())->toBe('most likes this week first')
        ->and(publicDeckQuery([])->sortNote())->toBe('newest first');
});

test("a profile's query only carries the sort and the series", function () {
    $query = publicDeckQuery(['q' => 'burn', 'sort' => 'liked', 'game' => 'magic', 'format' => 'modern'])->forUser(User::factory()->create());

    expect($query->queryWith())->toBe(['sort' => 'liked', 'game' => 'magic'])
        ->and($query->isFiltering())->toBeTrue();
});

test('url links to the page the query is for, with the changed filters', function () {
    $user = User::factory()->create(['username' => 'deckmaster']);
    $query = publicDeckQuery(['q' => 'burn', 'sort' => 'liked', 'game' => 'magic']);

    expect($query->url(['sort' => 'trending']))->toBe(route('decks.index', ['q' => 'burn', 'sort' => 'trending', 'game' => 'magic']))
        ->and($query->forUser($user)->url(['game' => null]))->toBe(route('users.decks', [$user, 'sort' => 'liked']));
});

test('a deck knows its series label and a tooltip for its format', function () {
    $casual = Deck::factory()->make(['game' => 'yugioh', 'format' => null]);
    $tcg = Deck::factory()->make(['game' => 'yugioh', 'format' => 'tcg']);

    expect($casual->series_label)->toBe('Yu-Gi-Oh!')
        ->and(Deck::factory()->make(['game' => 'magic'])->series_label)->toBe('Magic: The Gathering')
        ->and($casual->format_title)->toBe('No format: only the normal deck rules apply.')
        ->and($tcg->format_title)->toBe('Built for TCG Advanced.');
});

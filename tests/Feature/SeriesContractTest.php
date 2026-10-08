<?php

use App\Cards\CardMapper;
use App\Contracts\CardProvider;
use App\Contracts\PackProvider;
use App\Decks\DeckFormat;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * Every series in config/series.php must be complete. When adding a series (see docs/adding-a-series.md),
 * this test lists anything that's missing.
 */
test('every configured series is fully set up', function () {
    $problems = [];

    foreach (config('series') as $series => $config) {
        $check = function (bool $ok, string $problem) use (&$problems, $series) {
            if (! $ok) {
                $problems[] = "[$series] $problem";
            }
        };
        $implements = fn (?string $class, string $interface) => $class && class_exists($class) && is_subclass_of($class, $interface);

        foreach (['label', 'logo', 'tagline', 'related_label'] as $key) {
            $check(is_string($config[$key] ?? null) && $config[$key] !== '', "'$key' must be a non-empty string");
        }
        $check(($config['link_prefix'] ?? null) === "/$series", "'link_prefix' must be '/$series'");
        $check(is_file(resource_path('img/'.($config['logo'] ?? ''))), "logo resources/img/{$config['logo']} does not exist");

        $check($implements($config['provider'] ?? null, CardProvider::class), "'provider' must implement CardProvider");
        $check($implements($config['mapper'] ?? null, CardMapper::class), "'mapper' must extend CardMapper");
        $check($implements($config['deck_format'] ?? null, DeckFormat::class), "'deck_format' must implement DeckFormat");
        $check($implements($config['pack']['provider'] ?? null, PackProvider::class), "'pack.provider' must implement PackProvider");
        $check(($config['pack']['link_prefix'] ?? null) === "/$series", "'pack.link_prefix' must be '/$series'");

        foreach ($config['filters'] ?? [] as $key => $filter) {
            $check(is_string($filter['label'] ?? null), "filter '$key' needs a 'label'");
        }

        $deck = $config['deck'] ?? [];
        $check(isset($deck['zones']['main']), "'deck.zones' needs a 'main' zone");
        foreach ($deck['zones'] ?? [] as $zone => $rules) {
            $check(isset($rules['label'], $rules['min'], $rules['max']) && $rules['min'] <= $rules['max'], "zone '$zone' needs a label and min <= max");
        }
        $check(is_int($deck['max_copies'] ?? null) && $deck['max_copies'] >= 1, "'deck.max_copies' must be a positive number");
        $check(is_array($deck['extra_types'] ?? null) && is_array($deck['unlimited_types'] ?? null), "'deck.extra_types' and 'deck.unlimited_types' must be lists");
        $check(is_array($deck['formats'] ?? null) && $deck['formats'] !== [], "'deck.formats' must list at least one format");
        foreach ($deck['formats'] ?? [] as $key => $format) {
            $check(is_string($format['label'] ?? null), "format '$key' needs a 'label'");
        }
        $check(
            is_array($deck['limit_labels'] ?? null) && $deck['limit_labels'] !== []
                && collect($deck['limit_labels'])->every(fn ($label, $limit) => is_int($limit) && is_string($label)),
            "'deck.limit_labels' must map copy limits to labels"
        );
    }

    foreach (config('coming_soon') as $series => $config) {
        if (array_key_exists($series, config('series'))) {
            $problems[] = "[$series] is live, so remove it from config/coming_soon.php";
        }
        if (! is_file(resource_path('img/'.($config['logo'] ?? '')))) {
            $problems[] = "[$series] coming-soon logo resources/img/{$config['logo']} does not exist";
        }
    }

    expect($problems)->toBe([]);
});

test('the homepage lists every live and upcoming series', function () {
    $response = $this->get('/')->assertOk();

    foreach (config('series') as $series => $config) {
        $response->assertSee(route('cards.index', $series))->assertSee($config['tagline']);
    }

    foreach (config('coming_soon') as $config) {
        $response->assertSee($config['label'])->assertSee('Coming Soon');
    }
});

test('the dashboard shows random cards from every series', function () {
    Http::fake(['api.scryfall.com/cards/random' => Http::response([
        'id' => '00000000-0000-0000-0000-000000000001', 'name' => 'Random Magic Card',
    ])]);
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Random Magic Card')
        ->assertSee('Dark Magician')
        ->assertSee(route('cards.show', ['yugioh', '46986414']));
});

test('card ids are not limited to the formats of the current series', function () {
    // Pokémon TCG ids look like "swsh4-25"; each provider validates its own id format.
    $this->get('/yugioh/card/swsh4-25')->assertNotFound();
    $this->get('/magic/card/swsh4-25')->assertNotFound();

    $this->actingAs(User::factory()->create())
        ->post(route('decks.store', 'yugioh'), ['cards' => json_encode(['main' => [['id' => 'swsh4-25']]])])
        ->assertSessionHasErrors(['cards' => 'Some cards could not be found: swsh4-25']);
});

test('the site shows the fan content disclaimers', function () {
    $this->get(route('cards.index', 'yugioh'))
        ->assertSee('unofficial Fan Content permitted under the Fan Content Policy')
        ->assertSee('not affiliated with or endorsed by Konami');
});

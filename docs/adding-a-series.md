# Adding a card series

Everything series-specific lives behind four small classes and one config entry. Routes, navigation, the card
database, pack pages, the deck builder, deck validation, import/export and the dashboard all follow from
`config/series.php`.

`tests/Feature/SeriesContractTest.php` checks every configured series, so after adding one, run:

```bash
php artisan test --filter=SeriesContract
```

It lists anything missing, e.g. `[pokemon] 'deck_format' must implement DeckFormat`.

The steps below use **Pokémon** as the example, with data from [pokemontcg.io](https://pokemontcg.io)
(free API key, 20,000 requests a day).

## 1. Decide where the data comes from

| | Import into the database | Call the API live |
|---|---|---|
| Use when | The API has strict rate limits, or its terms ask you to store data locally | The API is built for live use and its search is better than ours |
| Example | Yu-Gi-Oh! (`YugiohService`, `yugioh:import`) | Magic (`MagicService`, Scryfall) |

For Pokémon, import: the card list is small (~20,000 cards) and the daily quota is easy to exceed with live
searches. Check the source's terms for images too. YGOPRODeck, for example, forbids hotlinking, which is why
`yugioh:images` exists.

## 2. Store the cards (when importing)

Copy the Yu-Gi-Oh! pattern:

- A migration for a `pokemon_cards` table with an indexed column per filter (`supertype`, `types`, `rarity`,
  `set_id`...) and the full card in a `json` `data` column. See `create_yugioh_cards_table`.
- A model with `attributesFrom(array $card)` and `toCardArray()`. See `App\Models\YugiohCard`.
- An import command, e.g. `pokemon:import`. See `App\Console\Commands\ImportYugiohCards`.

Keep queries portable: production runs on Postgres and tests on SQLite. Use `whereLike(..., caseSensitive: false)`
for searches.

## 3. The four classes

| Class | Interface | Responsible for | Example to copy |
|---|---|---|---|
| `App\Services\PokemonService` | `App\Contracts\CardProvider` | search, find, findMany, related, random, filterOptions | `YugiohService` |
| `App\Cards\PokemonCardMapper` | extends `App\Cards\CardMapper` | reading a raw card: image, type, stats, rarity, price... | `YugiohCardMapper` |
| `App\Services\PokemonPackService` | `App\Contracts\PackProvider` | sets: list, find, cards in a set | `YugiohPackService` |
| `App\Decks\PokemonLiveFormat` | `App\Decks\DeckFormat` | deck import/export in a format other tools read | `MagicTextFormat` |

Notes for Pokémon:

- **IDs** look like `swsh4-25`. Card IDs may contain letters, digits, `-`, `_` and `.`; `find()` should
  reject anything else with a 404 (see `YugiohService::find()` and `MagicService::find()`).
- **Mapper fields**: `images.large` / `images.small`, `supertype` + `subtypes` for type/subtype, `hp` and the
  attacks for stats, `rarity`, `set.name`, `tcgplayer.prices`. `fullType()` (type + subtype) is what deck rules
  match against, so make sure it contains words like "Basic Energy".
- **Deck format**: Pokémon TCG Live exports lists like `4 Pikachu SVI 63` under `Pokémon:`, `Trainer:` and
  `Energy:` headings.

## 4. Config

Add an entry to `config/series.php` (and remove it from `config/coming_soon.php`):

```php
'pokemon' => [
    'label' => 'Pokémon',
    'logo' => 'pokemon.png', // in resources/img
    'tagline' => 'Gotta catch ’em all!',
    'related_label' => 'More from this set',
    'provider' => PokemonService::class,
    'mapper' => PokemonCardMapper::class,
    'deck_format' => PokemonLiveFormat::class,
    'link_prefix' => '/pokemon',

    'rarity_colors' => ['common' => 'text-gray-600', 'uncommon' => 'text-green-700', 'rare' => 'text-yellow-600'],

    // Filter dropdowns, in display order. Keys are the search params your provider understands.
    'filters' => [
        'supertype' => ['label' => 'Card type'],
        'types' => ['label' => 'Energy type'],
        'rarity' => ['label' => 'Rarity'],
    ],

    // Enforced by the server and shared with the deck builder.
    'deck' => [
        'zones' => [
            'main' => ['label' => 'Deck', 'min' => 60, 'max' => 60],
        ],
        'max_copies' => 4,
        'extra_types' => [],
        'unlimited_types' => ['Basic Energy'],
    ],

    'pack' => [
        'provider' => PokemonPackService::class,
        'link_prefix' => '/pokemon',
        // Field names in the arrays your PackProvider returns.
        'code' => 'id',
        'name' => 'name',
        'release_date' => 'releaseDate',
        'card_count' => 'total',
        'image' => 'images.logo',
    ],
],
```

Config holds plain values only (no closures) so `php artisan config:cache` keeps working. `ConfigTest`
checks this.

## 5. Finishing touches

- Add the logo to `resources/img` (your own artwork, not the official logo; fan content policies usually
  forbid official logos).
- Add a trademark/fan-content line for the series to `resources/views/components/footer.blade.php`.
- Add tests for the new provider, mapper and deck format. `tests/Feature/BrowsingTest.php`,
  `YugiohSetsTest.php` and `DeckTransferTest.php` show the patterns; fake external APIs with `Http::fake()`.

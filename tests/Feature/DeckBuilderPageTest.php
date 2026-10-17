<?php

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use App\ViewModels\CardViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_prices' => [['cardmarket_price' => '0.10']]])->create();
    YugiohCard::factory()->fromCard([
        'id' => 79571449, 'name' => 'Graceful Charity', 'type' => 'Spell Card', 'race' => 'Normal',
        'banlist_info' => ['ban_tcg' => 'Limited', 'ban_ocg' => 'Semi-Limited'],
    ])->create();
});

/** The deck builder's JSON state block. */
function builderStateFrom(string $html): array
{
    preg_match('#<script type="application/json" id="deck-builder-state">(.*?)</script>#s', $html, $match);

    return json_decode($match[1] ?? 'null', true) ?? [];
}

/** What the builder's save form sends as `cards`: {"main": [{"id": "..."}, ...], ...}. */
function builderPayload(array $zones): string
{
    return json_encode(collect($zones)->map(fn (array $ids) => array_map(fn ($id) => ['id' => $id], $ids))->all());
}

function builderDeck(User $user): Deck
{
    $deck = Deck::factory()->for($user)->create(['game' => 'yugioh', 'format' => 'tcg', 'name' => 'Spellcasters']);

    foreach ([['46986414', 'Dark Magician', 2], ['79571449', 'Graceful Charity', 1]] as [$id, $name, $count]) {
        $card = Card::create(['game' => 'yugioh', 'external_id' => $id, 'name' => $name]);
        $deck->deckCards()->create(['card_id' => $card->id, 'zone' => 'main', 'count' => $count]);
    }

    return $deck;
}

test('a new deck starts from the series rules with empty zones', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('decks.builder', 'yugioh'))->assertOk()->getContent();
    $state = builderStateFrom($html);

    expect($state['rules']['formats']['tcg']['label'])->toBe('TCG Advanced')
        ->and($state['rules']['limit_labels'])->toBe(['Forbidden', 'Limited', 'Semi-Limited'])
        ->and($state['zones'])->toBe(['main' => [], 'extra' => [], 'side' => []])
        ->and($state['format'])->toBe('')
        ->and($state['editing'])->toBeFalse()
        ->and($state['currency'])->toBe(['code' => 'EUR', 'symbol' => '€']);

    expect($html)->toContain('data-import-toggle')
        ->toContain('<option value="tcg">TCG Advanced</option>')
        ->toContain('<option value="ocg">OCG</option>')
        ->toContain('action="'.route('decks.store', 'yugioh').'"');
});

test('editing a deck loads its cards, prices, limits and format', function () {
    $user = User::factory()->create();
    $deck = builderDeck($user);

    $html = $this->actingAs($user)->get(route('decks.edit', $deck))->assertOk()->getContent();
    $state = builderStateFrom($html);

    expect($state['format'])->toBe('tcg')
        ->and($state['editing'])->toBeTrue()
        ->and($state['title'])->toBe('Spellcasters')
        ->and(array_column($state['zones']['main'], 'id'))->toBe(['46986414', '46986414', '79571449'])
        ->and($state['zones']['main'][0]['price'])->toBe(0.1)
        ->and($state['zones']['main'][2]['limits'])->toBe(['tcg' => 1, 'ocg' => 2]);

    expect($html)->toContain('data-import-toggle')
        ->toContain('action="'.route('decks.update', $deck).'"')
        ->toContain('name="_method" value="PATCH"');
});

test('after a failed save the submitted format wins', function () {
    $user = User::factory()->create();
    $deck = builderDeck($user);

    $html = $this->actingAs($user)->withSession(['_old_input' => ['format' => 'ocg']])->get(route('decks.edit', $deck))->getContent();

    expect(builderStateFrom($html)['format'])->toBe('ocg');
});

test('search results carry their card data for the builder', function () {
    $card = new CardViewModel(YugiohCard::find(79571449)->toCardArray(), config('series.yugioh'));
    $json = Js::from($card->builderCard())->toHtml();

    $this->actingAs(User::factory()->create())->get(route('decks.builder', 'yugioh'))->assertSee($json, false);
    $this->get(route('decks.builder', ['yugioh', 'page' => 1]), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()->assertSee($json, false)->assertDontSee('<html', false);
});

test('the filter panel lists the series filters', function () {
    $this->actingAs(User::factory()->create())->get(route('decks.builder', 'yugioh'))
        ->assertSee('id="filterPanel"', false)
        ->assertSee('x-model="filters.attribute"', false)
        ->assertSee('x-model="filters.archetype"', false)
        ->assertSeeInOrder(['Type', 'Attribute', 'Race', 'Archetype']);
});

test('new decks import into a new deck, edited decks replace their cards', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('decks.builder', 'yugioh'))
        ->assertSee('id="importPanel"', false)
        ->assertSee('action="'.route('decks.import', 'yugioh').'"', false)
        ->assertSee('Upload or paste a .ydk file')
        ->assertSee('accept=".ydk"', false);

    $deck = builderDeck($user);
    $this->actingAs($user)->get(route('decks.edit', $deck))
        ->assertSee('id="importPanel"', false)
        ->assertSee('action="'.route('decks.reimport', $deck).'"', false)
        ->assertDontSee('action="'.route('decks.import', 'yugioh').'"', false);
});

test('the import panel opens again after a failed import', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->from(route('decks.builder', 'yugioh'))
        ->post(route('decks.import', 'yugioh'), [])->assertSessionHasErrors('list');

    $html = $this->actingAs($user)->get(route('decks.builder', 'yugioh'))->getContent();
    expect(builderStateFrom($html)['showImport'])->toBeTrue();
});

test('deck cards and search results share one card preview', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('decks.builder', 'yugioh'))->getContent();

    expect($html)->toContain('data-card-preview')
        ->toContain('@mouseenter="showPreview(card, $el)"')
        ->toContain('@focus="showPreview(card, $el)"');
});

test('the cover is picked from the deck in a dialog', function () {
    $this->actingAs(User::factory()->create())->get(route('decks.builder', 'yugioh'))
        ->assertSee('data-cover-picker-open', false)
        ->assertSee('id="coverPicker"', false)
        ->assertSee('Pick a cover card')
        ->assertSee('Remove cover');
});

test('guests see the builder locked behind a log in prompt', function () {
    $this->get(route('decks.builder', 'yugioh'))->assertOk()->assertSee('Log in to build decks');
});

test('the server accepts what the builder sends', function (string $format) {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), [
        'deck_title' => 'From the builder',
        'deck_description' => '',
        'is_public' => '0',
        'image' => '',
        'format' => $format,
        'cards' => builderPayload(['main' => ['46986414', '46986414', '79571449'], 'extra' => [], 'side' => []]),
    ])->assertSessionHasNoErrors();

    $deck = $user->decks()->sole();
    expect($deck->format)->toBe($format ?: null)
        ->and($deck->deckCards()->sum('count'))->toBe(3);
})->with(['casual' => [''], 'tcg' => ['tcg']]);

test('the builder starts with comments allowed for new decks and the saved value when editing', function () {
    $user = User::factory()->create();
    $deck = builderDeck($user);
    $deck->update(['allow_comments' => false]);

    $new = $this->actingAs($user)->get(route('decks.builder', 'yugioh'))->getContent();
    expect(builderStateFrom($new)['allowComments'])->toBeTrue()
        ->and($new)->toContain('x-model="allowComments"')->toContain('name="allow_comments"');

    expect(builderStateFrom($this->actingAs($user)->get(route('decks.edit', $deck))->getContent())['allowComments'])->toBeFalse();

    $failed = $this->actingAs($user)->withSession(['_old_input' => ['allow_comments' => '1']])->get(route('decks.edit', $deck))->getContent();
    expect(builderStateFrom($failed)['allowComments'])->toBeTrue();
});

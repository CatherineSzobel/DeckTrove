<?php

use App\Services\DeckService;
use App\ViewModels\CardViewModel;

/** The shared cases in tests/fixtures/deck-copy-rules.json; resources/js/deckbuilder/rules-parity.test.js runs them too. */
function deckCopyRules(): array
{
    // Not base_path(): the dataset is read before the app boots.
    return json_decode(file_get_contents(__DIR__.'/../fixtures/deck-copy-rules.json'), true);
}

test('the shared copy-rule cases use the Yu-Gi-Oh! deck config', function () {
    $config = config('series.yugioh.deck');
    $rules = deckCopyRules()['rules'];

    expect($rules['max_copies'])->toBe($config['max_copies'])
        ->and($rules['unlimited_types'])->toBe($config['unlimited_types'])
        ->and($rules['extra_types'])->toBe($config['extra_types'])
        ->and(array_map(fn ($format) => $format['label'], $rules['formats']))
        ->toBe(array_map(fn ($format) => $format['label'], $config['formats']));
});

test('the server words copy limits like the builder', function (array $case) {
    $statuses = [0 => 'Forbidden', 1 => 'Limited', 2 => 'Semi-Limited'];
    $card = new CardViewModel([
        'id' => 1,
        'name' => 'Test Card',
        'type' => $case['type'],
        'banlist_info' => collect($case['limits'])->mapWithKeys(fn (int $limit, string $format) => ["ban_$format" => $statuses[$limit]])->all(),
    ], config('series.yugioh'));

    expect($card->copyLimits())->toBe($case['limits']);

    $errors = app(DeckService::class)->copyErrors('yugioh', $case['format'] ?: null, ['1' => $case['copies']], collect(['1' => $card]));

    expect($errors['1'] ?? null)->toBe($case['error']);
})->with(fn () => collect(deckCopyRules()['cases'])->mapWithKeys(fn (array $case) => [$case['case'] => [$case]])->all());

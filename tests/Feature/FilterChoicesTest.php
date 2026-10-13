<?php

use App\Cards\FilterChoices;

test('filter choices follow the series config, with nicer labels where config has them', function () {
    $choices = FilterChoices::for('magic', [
        'type' => ['Creature', 'Instant'],
        'color' => ['R', 'U', 'X'],
        'set_name' => ['dmu' => 'Dominaria United'],
    ]);

    expect(array_keys($choices))->toBe(['type', 'color', 'rarity', 'set_name'])
        ->and($choices['type'])->toBe(['label' => 'Type', 'choices' => ['Creature' => 'Creature', 'Instant' => 'Instant']])
        ->and($choices['color']['choices'])->toBe(['R' => 'Red', 'U' => 'Blue', 'X' => 'X'])
        ->and($choices['rarity'])->toBe(['label' => 'Rarity', 'choices' => []])
        ->and($choices['set_name']['choices'])->toBe(['dmu' => 'Dominaria United']);
});

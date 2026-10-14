<?php

use Illuminate\Support\Facades\Blade;

test('the screenshots have default alt texts and can be given others', function () {
    expect(Blade::render('<x-showcase-shots />'))
        ->toContain('class="showcase-glow" aria-hidden="true"')
        ->toContain('alt="DeckTrove card database" data-shot="1" class="showcase-shot"')
        ->toContain('alt="DeckTrove deck builder" data-shot="2" class="showcase-shot"');

    expect(Blade::render('<x-showcase-shots :alts="[\'First\', \'Second\']" />'))
        ->toContain('alt="First" data-shot="1"')
        ->toContain('alt="Second" data-shot="2"');
});

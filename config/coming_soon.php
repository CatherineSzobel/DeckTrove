<?php

/**
 * Series shown as "coming soon" on the homepage and in the series selector.
 *
 * When a series goes live, move it to config/series.php and remove it here.
 */
return [

    'pokemon' => [
        'label' => 'Pokémon',
        'logo' => 'pokemon.png', // in resources/img
        'tagline' => 'Catch ’em all soon!',
    ],

    'digimon' => [
        'label' => 'Digimon',
        'logo' => 'digimon.png',
        'tagline' => 'Digital monsters arriving soon!',
    ],

];

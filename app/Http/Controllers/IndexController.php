<?php

namespace App\Http\Controllers;

class IndexController extends Controller
{

    public function index()
    {
        $TCGs = [
            ['src' => 'magic.png', 'series' => 'Magic the Gathering', 'comingSoon' => false, 'link' => 'magic', 'description' => 'Cast spells and control the battlefield!'],
            ['src' => 'yugioh.png', 'series' => 'Yu-Gi-Oh', 'comingSoon' => false, 'link' => 'yugioh', 'description' => 'Duel your way to victory soon!'],
            ['src' => 'pokemon.png', 'series' => 'Pokémon', 'comingSoon' => true, 'link' => 'pokemon', 'description' => 'Catch ’em all soon!'],
            ['src' => 'digimon.png', 'series' => 'Digimon', 'comingSoon' => true, 'link' => 'digimon', 'description' => 'Digital monsters arriving soon!'],
        ];
        return view('index', ['tcgs' => $TCGs]);
    }
}

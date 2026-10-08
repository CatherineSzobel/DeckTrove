<?php

namespace App\Http\Controllers;

class IndexController extends Controller
{
    public function __invoke()
    {
        $live = collect(config('series'))->map(fn (array $config, string $series) => [
            'src' => $config['logo'],
            'series' => $config['label'],
            'comingSoon' => false,
            'link' => $series,
            'description' => $config['tagline'],
        ]);

        $upcoming = collect(config('coming_soon'))->map(fn (array $config, string $series) => [
            'src' => $config['logo'],
            'series' => $config['label'],
            'comingSoon' => true,
            'link' => $series,
            'description' => $config['tagline'],
        ]);

        return view('index', ['tcgs' => $live->merge($upcoming)->values()]);
    }
}

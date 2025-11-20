<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

class MagicPackController extends Controller
{
    public function index(Request $request)
    {
        $series = 'magic';
        $page = $request->get('page', 1);
        $search = $request->get('search', '');

        // Cache sets for 1 hour
        $sets = cache()->remember('magic-sets', 3600, function () {
            $response = Http::get('https://api.scryfall.com/sets');

            if ($response->failed()) {
                abort(500, 'Failed to fetch sets from Scryfall');
            }

            return $response->json()['data'] ?? [];
        });

        // Filter by search if provided
        if ($search) {
            $sets = array_values(array_filter($sets, function ($set) use ($search) {
                return stripos($set['name'], $search) !== false ||
                    stripos($set['code'], $search) !== false;
            }));
        }

        // Custom pagination
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $paged = array_slice($sets, $offset, $perPage);

        $paginator = new LengthAwarePaginator(
            $paged,
            count($sets),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('packs.packs', [
            'packs' => $paginator,
            'series' => $series
        ]);
    }

    public function show($code)
    {
        $response = Http::get("https://api.scryfall.com/sets/{$code}");

        if ($response->failed()) {
            abort(404, 'Set not found');
        }

        $set = $response->json();

        // Fetch cards from the set
        $cardsResponse = Http::get('https://api.scryfall.com/cards/search', [
            'q' => 'set:' . strtolower($code)
        ]);

        $cards = $cardsResponse->json()['data'] ?? [];

        return view('packs.pack', [
            'set' => $set,
            'cards' => $cards,
            'series' => 'magic'
        ]);
    }
}

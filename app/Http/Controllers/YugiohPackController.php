<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class YugiohPackController extends Controller
{
    public $packsPath;
    public $packs;

    public function __construct()
    {
        $this->packsPath = public_path('json/yugioh-packs.json'); // main packs info
        $this->packs = json_decode(file_get_contents($this->packsPath), true);
    }

    public function index(Request $request)
    {
        $series = 'yugioh';
        if (!file_exists($this->packsPath)) {
            abort(404, 'Packs file not found');
        }

        // Load packs from JSON file
        $packsCollection = collect($this->packs)->sortBy('release_date', SORT_NATURAL | SORT_FLAG_CASE);

        // Pagination values
        $page = $request->get('page', 1);
        $perPage = 24;

        // Slice data for current page
        $currentPageItems = $packsCollection->slice(($page - 1) * $perPage, $perPage)->values();

        // Build paginator
        $paginated = new LengthAwarePaginator(
            $currentPageItems,
            $packsCollection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('packs.packs', [
            'packs' => $paginated,
            'series' => $series
        ]);
    }


    public function show($code)
    {
        // Load pack info
        $pack = collect($this->packs)->firstWhere('set_code', $code);

        if (!$pack) {
            Log::warning("Pack not found", ['code' => $code]);
            abort(404, 'Pack not found');
        }

        // Load only the cards for this pack
        $packFile = public_path("json/packs/{$code}.json");

        if (!file_exists($packFile)) {
            Log::warning("Pack JSON file not found", ['file' => $packFile]);
            $cards = collect(); // empty collection
        } else {
            $cards = collect(json_decode(file_get_contents($packFile), true));
        }

        return view('packs.pack', [
            'pack' => (object)$pack,
            'cards' => $cards,
            'series' => 'yugioh'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class YugiohPackController extends Controller
{
    public $packsPath;

    public function __construct()
    {
        $this->packsPath = public_path('json/yugioh-packs.json'); // main packs info
    }

    public function index(Request $request)
    {
        $series = 'yugioh';
        if (!file_exists($this->packsPath)) {
            abort(404, 'Packs file not found');
        }

        // Load packs from JSON file
        $packs = collect(json_decode(file_get_contents($this->packsPath), true));

        // Pagination values
        $page = $request->get('page', 1);
        $perPage = 24;

        // Slice data for current page
        $currentPageItems = $packs->slice(($page - 1) * $perPage, $perPage)->values();

        // Build paginator
        $paginated = new LengthAwarePaginator(
            $currentPageItems,
            $packs->count(),  // total items
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('packs.packs', [
            'packs' => $paginated, // this is now the paginator
            'series' => $series
        ]);
    }


    public function show($code)
    {
        // Load pack info
        $packs = json_decode(file_get_contents($this->packsPath), true);
        $pack = collect($packs)->firstWhere('set_code', $code);

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
            Log::info("Pack Loaded pack cards", [
                'pack_code' => $code,
                'count' => $cards->count()
            ]);
        }

        return view('packs.pack', [
            'pack' => (object)$pack,
            'cards' => $cards,
            'series' => 'yugioh'
        ]);
    }
}

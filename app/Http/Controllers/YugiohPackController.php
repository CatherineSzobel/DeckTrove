<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\YugiohPackService;

class YugiohPackController extends Controller
{
    public function index(Request $request, YugiohPackService $service)
    {
        $currentView = request('view', 'full');
        $series = 'yugioh';
        $paginated = $service->paginatePacks($request);

        return view('packs.packs', [
            'packs' => $paginated,
            'series' => $series,
            'currentView' => $currentView
        ]);
    }

    public function show(string $code, YugiohPackService $service)
    {
        $pack = $service->findPack($code);

        if (!$pack) {
            abort(404, 'Pack not found');
        }

        $cards = $service->loadPackCards($code);

        return view('packs.pack', [
            'pack' => $pack,
            'cards' => $cards,
            'series' => 'yugioh'
        ]);
    }
}

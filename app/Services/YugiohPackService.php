<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class YugiohPackService
{
    private string $packsPath;
    private array $packs;

    public function __construct()
    {
        $this->packsPath = public_path('json/yugioh-packs.json');

        if (!file_exists($this->packsPath)) {
            $this->packs = [];
            return;
        }

        $this->packs = json_decode(file_get_contents($this->packsPath), true);
    }

    /**
     * Get all packs as a sorted collection
     */
    public function getPacks(): Collection
    {
        return collect($this->packs)
            ->sortBy('release_date', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * Paginate packs
     */
    public function paginatePacks(Request $request, int $perPage = 24): LengthAwarePaginator
    {
        $page = $request->get('page', 1);
        $packsCollection = $this->getPacks();

        $currentPageItems = $packsCollection->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentPageItems,
            $packsCollection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * Find a single pack by set code
     */
    public function findPack(string $code): ?object
    {
        $pack = collect($this->packs)->firstWhere('set_code', $code);

        if (!$pack) {
            Log::warning("Pack not found", ['code' => $code]);
            return null;
        }

        return (object)$pack;
    }

    /**
     * Load cards for a specific pack
     */
    public function loadPackCards(string $code): Collection
    {
        $packFile = public_path("json/packs/{$code}.json");

        if (!file_exists($packFile)) {
            Log::warning("Pack JSON file not found", ['file' => $packFile]);
            return collect();
        }

        return collect(json_decode(file_get_contents($packFile), true));
    }
}

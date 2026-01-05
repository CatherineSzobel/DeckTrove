<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class YugiohPackService
{

    public function __construct(protected string $packsPath = '', protected array $packs = [])
    {
        $this->packsPath = public_path('json/yugioh-packs.json');

        if (!file_exists($this->packsPath)) {
            $this->packs = [];
            return;
        }

        $this->packs = json_decode(file_get_contents($this->packsPath), true);
    }

    public function getPacks(): Collection
    {
        return collect($this->packs)
            ->sortBy('release_date', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function fetchPacksByPagination(array $params, int $perPage = 24): LengthAwarePaginator
    {
        $page = $params['page'] ?? 1;
        $packsCollection = $this->getPacks();

        $currentPageItems = $packsCollection->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentPageItems,
            total: $packsCollection->count(),
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => $params['url'] ?? '',
                'query' => $params,
            ]
        );
    }

    public function getPackById(string $code): ?array
    {
        $pack = collect($this->packs)->firstWhere('set_code', $code);

        if (!$pack) {
            Log::warning("Pack not found", ['code' => $code]);
            return null;
        }

        return $pack;
    }

    public function getPackCards(string $code): Collection
    {
        $packFile = public_path("json/packs/{$code}.json");

        if (!file_exists($packFile)) {
            Log::warning("Pack JSON file not found", ['file' => $packFile]);
            return collect();
        }

        return collect(json_decode(file_get_contents($packFile), true));
    }
}

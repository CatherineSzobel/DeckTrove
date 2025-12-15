<?php 
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MagicPackService
{
    public function getSets(): array
    {
        return Cache::remember('magic-sets', 3600, function () {
            $response = Http::get('https://api.scryfall.com/sets');

            if ($response->failed()) {
                abort(500, 'Failed to fetch sets from Scryfall');
            }

            return $response->json()['data'] ?? [];
        });
    }

    public function searchSets(string $search, array $sets): array
    {
        if (!$search) return $sets;

        return array_values(array_filter($sets, function ($set) use ($search) {
            return stripos($set['name'], $search) !== false ||
                   stripos($set['code'], $search) !== false;
        }));
    }

    public function getSet(string $code): array
    {
        $response = Http::get("https://api.scryfall.com/sets/{$code}");

        if ($response->failed()) {
            abort(404, 'Set not found');
        }

        return $response->json();
    }

    public function getSetCards(string $code): array
    {
        $response = Http::get('https://api.scryfall.com/cards/search', [
            'q' => 'set:' . strtolower($code)
        ]);

        return $response->json()['data'] ?? [];
    }
}

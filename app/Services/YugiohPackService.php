<?php

namespace App\Services;

use App\Contracts\PackProvider;
use App\Models\YugiohSet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Yu-Gi-Oh! sets from the yugioh_sets table (filled by `php artisan yugioh:import`).
 */
class YugiohPackService implements PackProvider
{
    public function paginate(string $search = '', int $page = 1, int $perPage = 24): LengthAwarePaginator
    {
        return YugiohSet::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->whereLike('name', "%$search%")
                ->orWhereLike('code', "%$search%")))
            ->orderByDesc('released_at')
            ->orderBy('name')
            ->paginate($perPage, page: $page)
            ->through(fn (YugiohSet $set) => $set->toPackArray());
    }

    public function find(string $code, ?string $slug = null): array
    {
        return $this->set($code, $slug)->toPackArray();
    }

    public function cards(string $code, ?string $slug = null): Collection
    {
        $set = $this->set($code, $slug);

        return $set->printings()
            ->with('card')
            ->orderBy('code')
            ->get()
            ->groupBy('yugioh_card_id')
            ->map(fn (Collection $printings) => $printings->first()->card->toCardArray() + [
                'printing' => [
                    'set_name' => $set->name,
                    'set_code' => $printings->first()->code,
                    // A card can be printed in several rarities in the same set.
                    'set_rarity' => $printings->pluck('rarity')->filter()->unique()->implode(' / '),
                ],
            ])
            ->values();
    }

    /**
     * Codes can be shared by several products, so the slug picks one; without it the newest is used.
     */
    private function set(string $code, ?string $slug): YugiohSet
    {
        return YugiohSet::where('code', $code)
            ->when($slug, fn ($query) => $query->where('slug', $slug))
            ->orderByDesc('released_at')
            ->first() ?? abort(404, 'Pack not found');
    }
}

<?php

namespace App\Services;

use App\Contracts\PackProvider;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Yu-Gi-Oh! sets from the local YGOPRODeck dump. Each set's card list lives in its own JSON file.
 */
class YugiohPackService implements PackProvider
{
    private ?Collection $packs = null;

    public function __construct(
        private readonly string $packsPath,
        private readonly string $packCardsDirectory,
    ) {}

    public function paginate(string $search = '', int $page = 1, int $perPage = 24): LengthAwarePaginator
    {
        $packs = $this->packs();

        if ($search !== '') {
            $packs = $packs->filter(fn ($pack) => stripos($pack['set_name'], $search) !== false
                || stripos($pack['set_code'], $search) !== false);
        }

        return new LengthAwarePaginator(
            $packs->forPage($page, $perPage)->values(),
            $packs->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()],
        );
    }

    public function find(string $code): array
    {
        return $this->packs()->firstWhere('set_code', $code) ?? abort(404, 'Pack not found');
    }

    public function cards(string $code): Collection
    {
        // Only codes from our own pack list map to files, so the code can never escape the directory.
        $code = $this->find($code)['set_code'];
        $file = $this->packCardsDirectory.DIRECTORY_SEPARATOR.$code.'.json';

        return is_file($file)
            ? collect(json_decode(file_get_contents($file), true) ?? [])
            : collect();
    }

    /**
     * All packs, newest first.
     */
    private function packs(): Collection
    {
        return $this->packs ??= collect(is_file($this->packsPath)
            ? json_decode(file_get_contents($this->packsPath), true) ?? []
            : [])
            ->sortByDesc('tcg_date')
            ->values();
    }
}

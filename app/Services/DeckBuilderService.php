<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;

class DeckBuilderService
{
    public function __construct(
        protected MagicService $magic,
        protected YugiohService $yugioh
    ) {}

    public function search(string $game, array $params): LengthAwarePaginator
    {
        return match ($game) {
            'magic'  => $this->magic->fetchCardsByPagination($params),
            'yugioh' => $this->yugioh->fetchCardsByPagination($params),
            default => abort(400, 'Unsupported game'),
        };
    }

    public function loadDeckCards(string $game, array $ids)
    {
        return match ($game) {
            'magic' => $this->magic->fetchCollection($ids),
            'yugioh' => collect($ids)->map(fn($id) => $this->yugioh->fetchCardById($id)),
            default => abort(400, 'Unsupported game'),
        };
    }

    public function filters(string $game): array
    {
        return match ($game) {
            'magic' => $this->magic->getFilterOptions(),
            'yugioh' => $this->yugioh->getFilterOptions(),
            default => [],
        };
    }
}

<?php

namespace App\Services;

use App\Exceptions\SeriesNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


class CardService
{
    public function __construct(protected MagicService $magicService, protected YugiohService $yugiohService) 
    {}

    public function getCardsBySeries(string $series, array $params): LengthAwarePaginator
    {
        return match ($series) {
            'magic' => $this->magicService->fetchCardsByPagination($params),
            'yugioh' => $this->yugiohService->fetchCardsByPagination($params),
            default => throw new SeriesNotFoundException("Unsupported series: $series"),
        };
    }
    public function getFiltersBySeries(string $series): array
    {
        return match ($series) {
            'magic' => $this->magicService->getFilterOptions(),
            'yugioh' => $this->yugiohService->getFilterOptions(),
            default => throw new SeriesNotFoundException("Unsupported series: $series"),
        };
    }
    public function fetchCardById(string|int $id, string $series) : array
    {
        return match ($series) {
            'magic' => $this->magicService->fetchCardById($id),
            'yugioh' => $this->yugiohService->fetchCardById($id),
            default => throw new SeriesNotFoundException("Unsupported series: $series"),
        };
    }

    public function getSetCardsBySeries(string $series, $card): Collection
    {
        return match ($series) {
            'magic' => $this->magicService->fetchRelatedSetCards($card),
            'yugioh' => $this->yugiohService->fetchRelatedSetCards($card),
            default => throw new SeriesNotFoundException("Unsupported series: $series"),
        };
    }

}
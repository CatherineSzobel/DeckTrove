<?php

namespace App\Services;

class PackService
{
    public function __construct(protected MagicPackService $magicPackService, protected YugiohPackService $yugiohPackService) 
    {}

    public function getPacksBySeries(string $series)
    {
        return match ($series) {
            'magic' => $this->magicPackService->getSets(),
            'yugioh' => $this->yugiohPackService->getPacks(),
            default => collect([]),
        };
    }
    public function getPackByIdBySeries(string $series, string $setCode)
    {
        return match ($series) {
            'magic' => $this->magicPackService->getSetById($setCode),
            'yugioh' => $this->yugiohPackService->getPackById($setCode), 
            default => [],
        };
    }
    public function getCardsFromSetBySeries(string $series, string $setCode)
    {
        return match ($series) {
            'magic' => $this->magicPackService->getSetCards($setCode),
            'yugioh' => $this->yugiohPackService->getPackCards($setCode), 
            default => [],
        };
    }

    public function getPaginatedPacksBySeries(string $series, array $params)
    {
        return match ($series) {
            'magic' => $this->magicPackService->fetchPacksByPagination($params),
            'yugioh' => $this->yugiohPackService->fetchPacksByPagination($params), 
            default => collect([]),
        };
    }

}
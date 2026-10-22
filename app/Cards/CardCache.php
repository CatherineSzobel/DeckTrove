<?php

namespace App\Cards;

use App\Models\Card;
use App\ViewModels\CardViewModel;
use Illuminate\Support\Collection;

/**
 * The local `cards` rows (name and image) that decks and collections point at, so they still show a card the source
 * has since dropped. One upsert for all cards, relying on the unique (game, external_id) index.
 */
final class CardCache
{
    /**
     * @param  Collection<string|int, array>  $raw  raw cards keyed by external id
     * @return Collection<string, int> card ids keyed by external id
     */
    public static function store(string $game, Collection $raw): Collection
    {
        $config = config("series.$game");
        $now = now();

        Card::upsert(
            $raw->map(function (array $card, $externalId) use ($game, $config, $now) {
                $viewModel = new CardViewModel($card, $config);

                return [
                    'game' => $game,
                    'external_id' => (string) $externalId,
                    'name' => $viewModel->name(),
                    'image_url' => $viewModel->image(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->values()->all(),
            ['game', 'external_id'],
            ['name', 'image_url', 'updated_at'],
        );

        return Card::where('game', $game)
            ->whereIn('external_id', $raw->keys()->map(fn ($id) => (string) $id))
            ->pluck('id', 'external_id')
            ->mapWithKeys(fn ($id, $externalId) => [(string) $externalId => $id]);
    }
}

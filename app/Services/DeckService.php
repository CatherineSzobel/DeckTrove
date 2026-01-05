<?php

namespace App\Services;

use App\Models\Deck;
use App\Models\Card;
use App\Models\DeckCard;
use Illuminate\Support\Facades\Auth;
use App\Services\MagicService;

class DeckService
{

    public function __construct(protected MagicService $magicService,protected YugiohService $yugiohService)
    {}
    public function save(array $data): Deck
    {
        if (!Auth::check()) abort(403);

        $deck = Deck::create([
            'user_id' => Auth::id(),
            'game' => $data['game'],
            'name' => $data['deck_title'] ?? 'New Deck',
            'description' => $data['deck_description'] ?? null,
            'image' => $data['image'] ?? null,
        ]);

        $this->syncCards($deck, $data['cards']);

        return $deck;
    }

    private function syncCards(Deck $deck, array $cards): void
    {
        foreach ($cards as $zone => $zoneCards) {
            $grouped = collect($zoneCards)->groupBy('id');

            foreach ($grouped as $externalId => $group) {
                $first = $group->first();

                $card = Card::firstOrCreate(
                    ['external_id' => $externalId, 'game' => $deck->game],
                    [
                        'name' => $first['name'] ?? 'Unknown',
                        'image_url' => $first['image_uris']['normal']
                            ?? $first['image']
                            ?? null,
                    ]
                );

                DeckCard::create([
                    'deck_id' => $deck->id,
                    'card_id' => $card->id,
                    'zone' => $zone,
                    'count' => $group->count(),
                ]);
            }
        }
    }

    function updateCards(Deck $deck, array $cards): void
    {
        $keepDeckCardIds = [];
        foreach ($cards as $zone => $zoneCards) {
            $grouped = collect($zoneCards)->groupBy('id');

            foreach ($grouped as $externalId => $group) {
                $first = $group->first();
                $card = Card::firstOrCreate(
                    ['external_id' => $externalId, 'game' => $deck->game],
                    [
                        'name' => $first['name'] ?? 'Unknown',
                        'image_url' => $first['image_uris']['normal'] ?? $first['image'] ?? null,
                    ]
                );
                $deckCard = DeckCard::updateOrCreate(
                    [
                        'deck_id' => $deck->id,
                        'card_id' => $card->id,
                        'zone' => $zone,
                    ],
                    [
                        'count' => $group->count(),
                    ]
                );

                $keepDeckCardIds[] = $deckCard->id;
            }
        }

        DeckCard::where('deck_id', $deck->id)
            ->whereNotIn('id', $keepDeckCardIds)
            ->delete();
    }

    public function buildSections(
        Deck $deck
    ): array {
        return match ($deck->game) {
            'magic' => $this->buildMagic($deck, $this->magicService),
            'yugioh' => $this->buildYugioh($deck, $this->yugiohService),
            default => ['main' => collect(), 'extra' => collect(), 'side' => collect()],
        };
    }

    private function buildMagic(Deck $deck, MagicService $magicService): array
    {
        $sections = ['main' => collect(), 'extra' => collect(), 'side' => collect()];
        $cards = $magicService->fetchCollection($deck->cards->pluck('external_id')->unique()->all());
        foreach ($deck->cards as $card) {
            if ($data = $cards->get($card->external_id)) {
                $sections[$card->pivot->zone]
                    ->push(...array_fill(0, $card->pivot->count, $data));
            }
        }

        return $sections;
    }

    private function buildYugioh(Deck $deck, YugiohService $yugiohService): array
    {
        $sections = ['main' => collect(), 'extra' => collect(), 'side' => collect()];

        foreach ($deck->cards as $card) {
            $data = $yugiohService->fetchCardById((int)$card->external_id);
            $sections[$card->pivot->zone]
                ->push(...array_fill(0, $card->pivot->count, $data));
        }

        return $sections;
    }
}

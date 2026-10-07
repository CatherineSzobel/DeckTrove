<?php

namespace App\Services;

use App\Models\Card;
use App\Models\Deck;
use App\ViewModels\CardViewModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeckService
{
    public function __construct(private readonly CardService $cards) {}

    /**
     * Create or update a deck and replace its cards.
     *
     * @param  array  $attributes  deck_title, deck_description, image, is_public
     * @param  array  $counts  card counts per zone: ['main' => ['<external id>' => 2, ...], ...]
     *
     * @throws ValidationException when a card is unknown or the deck breaks the game's rules
     */
    public function save(Deck $deck, array $attributes, array $counts): Deck
    {
        $rules = config("series.{$deck->game}.deck");
        $cards = $this->resolveCards($deck->game, $counts);

        $this->validateRules($rules, $counts, $cards);

        $mainCount = array_sum($counts['main'] ?? []);

        $deck->fill([
            'name' => ($attributes['deck_title'] ?? null) ?: ($deck->name ?? 'New Deck'),
            'description' => $attributes['deck_description'] ?? null,
            'image' => $attributes['image'] ?? null,
            // Decks below the minimum size can't be shared yet.
            'is_public' => ($attributes['is_public'] ?? false) && $mainCount >= $rules['zones']['main']['min'],
        ]);

        DB::transaction(function () use ($deck, $counts, $cards) {
            $deck->save();
            $this->syncCards($deck, $counts, $cards);
        });

        return $deck;
    }

    /**
     * The deck's cards as view models per zone, one entry per copy.
     *
     * @return array<string, Collection<int, CardViewModel>>
     */
    public function buildSections(Deck $deck): array
    {
        $config = config("series.{$deck->game}");
        $deck->loadMissing('cards');

        $data = $this->cards->for($deck->game)->findMany($deck->cards->pluck('external_id')->all());

        $sections = collect($config['deck']['zones'])->map(fn () => collect())->all();

        foreach ($deck->cards as $card) {
            // Fall back to what we stored locally if the source no longer has the card.
            $raw = $data->get($card->external_id) ?? ['id' => $card->external_id, 'name' => $card->name];
            $viewModel = new CardViewModel($raw, $config);

            $sections[$card->pivot->zone] ??= collect();
            $sections[$card->pivot->zone]->push(...array_fill(0, $card->pivot->count, $viewModel));
        }

        return $sections;
    }

    private function resolveCards(string $game, array $counts): Collection
    {
        $ids = collect($counts)->flatMap(fn ($zone) => array_keys($zone))->map(fn ($id) => (string) $id)->unique();
        $cards = $this->cards->for($game)->findMany($ids->all());

        $missing = $ids->reject(fn ($id) => $cards->has($id));

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'cards' => 'Some cards could not be found: '.$missing->take(5)->implode(', '),
            ]);
        }

        return $cards;
    }

    private function validateRules(array $rules, array $counts, Collection $cards): void
    {
        $errors = [];
        $typeOf = fn (string $id) => (string) ($cards[$id]['type_line'] ?? $cards[$id]['type'] ?? '');
        $nameOf = fn (string $id) => $cards[$id]['name'] ?? $id;
        $matches = fn (string $type, array $needles) => collect($needles)
            ->contains(fn ($needle) => stripos($type, $needle) !== false);

        foreach ($counts as $zone => $zoneCounts) {
            $zoneRules = $rules['zones'][$zone];
            $total = array_sum($zoneCounts);

            if ($total > $zoneRules['max']) {
                $errors[] = "{$zoneRules['label']} cannot have more than {$zoneRules['max']} cards.";
            }

            if (! $rules['extra_types']) {
                continue;
            }

            foreach (array_keys($zoneCounts) as $id) {
                $isExtra = $matches($typeOf((string) $id), $rules['extra_types']);

                // The side deck may hold anything; extra deck monsters can't go in the main deck.
                if ($zone === 'extra' && ! $isExtra) {
                    $errors[] = "{$nameOf((string) $id)} does not belong in the {$zoneRules['label']}.";
                } elseif ($zone === 'main' && $isExtra) {
                    $errors[] = "{$nameOf((string) $id)} belongs in the Extra Deck.";
                }
            }
        }

        $copies = collect($counts)->reduce(function (array $carry, array $zoneCounts) {
            foreach ($zoneCounts as $id => $count) {
                $carry[$id] = ($carry[$id] ?? 0) + $count;
            }

            return $carry;
        }, []);

        foreach ($copies as $id => $count) {
            if ($count > $rules['max_copies'] && ! $matches($typeOf((string) $id), $rules['unlimited_types'])) {
                $errors[] = "You can only have {$rules['max_copies']} copies of {$nameOf((string) $id)}.";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages(['cards' => $errors]);
        }
    }

    private function syncCards(Deck $deck, array $counts, Collection $cards): void
    {
        $config = config("series.{$deck->game}");

        $cardIds = $cards->map(function (array $raw, $externalId) use ($deck, $config) {
            $viewModel = new CardViewModel($raw, $config);

            return Card::updateOrCreate(
                ['game' => $deck->game, 'external_id' => (string) $externalId],
                ['name' => $viewModel->name(), 'image_url' => $viewModel->image()],
            )->id;
        });

        $deck->deckCards()->delete();

        foreach ($counts as $zone => $zoneCounts) {
            foreach ($zoneCounts as $externalId => $count) {
                $deck->deckCards()->create([
                    'card_id' => $cardIds[(string) $externalId],
                    'zone' => $zone,
                    'count' => $count,
                ]);
            }
        }
    }
}

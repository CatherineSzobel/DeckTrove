<?php

namespace App\Collection;

use App\Models\Deck;
use App\Models\User;
use App\Support\Money;

/** The deck page's "Your collection" block (resources/views/decks/partials/collection-block.blade.php). */
final class DeckCollection
{
    private function __construct(private DeckNeeds $needs) {}

    public static function for(?User $user, Deck $deck): ?self
    {
        return $user ? new self(app(CollectionService::class)->deckNeeds($user, $deck)) : null;
    }

    public function summary(): string
    {
        return $this->needs->isComplete()
            ? 'You own every card in this deck.'
            : "You own {$this->needs->owned} of {$this->needs->needed}";
    }

    public function percent(): int
    {
        return $this->needs->percent();
    }

    /** The progress bar's inline width, e.g. "width: 92%". */
    public function barStyle(): string
    {
        return "width: {$this->needs->percent()}%";
    }

    public function isComplete(): bool
    {
        return $this->needs->isComplete();
    }

    /** @return list<array{name: string, line: string}> */
    public function missing(): array
    {
        return array_map(fn (array $row) => ['name' => $row['name'], 'line' => "need {$row['need']} · own {$row['own']}"], $this->needs->missing);
    }

    /** "Missing cards: about €12.40", in the deck stats' money format; null when nothing is missing or priced. */
    public function costLine(): ?string
    {
        if ($this->needs->missing === [] || $this->needs->missingCost === null) {
            return null;
        }

        return 'Missing cards: about '.Money::format($this->needs->missingCost);
    }
}

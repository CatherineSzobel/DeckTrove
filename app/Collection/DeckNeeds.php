<?php

namespace App\Collection;

/**
 * How much of a deck a user owns: copies owned and needed (counted per card, so printings collapse), the cards still
 * missing, and what they cost (null when none of them has a price; $unpriced copies are left out of it).
 */
final readonly class DeckNeeds
{
    /**
     * @param  list<array{external_id: string, name: string, need: int, own: int}>  $missing
     */
    public function __construct(
        public int $owned,
        public int $needed,
        public array $missing,
        public ?float $missingCost,
        public int $unpriced,
    ) {}

    public function missingCount(): int
    {
        return $this->needed - $this->owned;
    }

    public function isComplete(): bool
    {
        return $this->missing === [];
    }

    public function percent(): int
    {
        return $this->needed === 0 ? 100 : intdiv($this->owned * 100, $this->needed);
    }
}

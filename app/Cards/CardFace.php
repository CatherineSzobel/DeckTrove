<?php

namespace App\Cards;

/**
 * One face of a double-faced card (Magic's transform, modal double-faced and battle cards), as the card page shows it:
 * the face's own name, image, type line, rules text and stats. Built by CardMapper::faces().
 */
final class CardFace
{
    /** @param  list<array{label: string, value: string}>  $stats  mana cost first, then the face's other stats */
    public function __construct(
        private string $name,
        private string $image,
        private string $typeLine,
        private string $text,
        private array $stats,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function image(): string
    {
        return $this->image;
    }

    public function typeLine(): string
    {
        return $this->typeLine;
    }

    public function text(): string
    {
        return $this->text;
    }

    /** @return list<array{label: string, value: string}> */
    public function detailStats(): array
    {
        return $this->stats;
    }
}

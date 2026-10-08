<?php

namespace App\Decks;

use App\Services\MagicService;
use Illuminate\Support\Collection;

/**
 * Plain-text Magic deck lists, as used by MTG Arena, MTGO and most deck sites:
 *
 *     Deck
 *     4 Lightning Bolt (2X2) 117
 *
 *     Sideboard
 *     2 Pyroblast
 *
 * Imports also accept MTGO-style lists without headers (the sideboard follows a blank line),
 * "SB:" prefixes, "4x" counts, and lines without a set and collector number.
 */
class MagicTextFormat implements DeckFormat
{
    private const LINE = '/^(?:SB:\s*)?(\d+)x?\s+(.+?)(?:\s+\(([A-Za-z0-9]{2,6})\)(?:\s+(\S+))?)?(?:\s+\*[A-Z]+\*)?$/u';

    public function __construct(private readonly MagicService $magic) {}

    public function extension(): string
    {
        return 'txt';
    }

    public function importHint(): string
    {
        return 'Paste a deck list from MTG Arena, MTGO or a deck site, e.g. "4 Lightning Bolt (2X2) 117", with a "Sideboard" section.';
    }

    public function export(array $zones, Collection $cards): string
    {
        $sections = [];

        foreach (['main' => 'Deck', 'side' => 'Sideboard'] as $zone => $header) {
            if (empty($zones[$zone])) {
                continue;
            }

            $lines = [$header];

            foreach ($zones[$zone] as $id => $count) {
                $lines[] = $this->line($count, $cards->get($id) ?? []);
            }

            $sections[] = implode("\n", $lines);
        }

        return implode("\n\n", $sections)."\n";
    }

    public function import(string $contents): ImportedDeck
    {
        $entries = $this->parse($contents);
        $cards = $this->resolve(array_column($entries, 'identifier'));
        $counts = [];
        $missing = [];

        foreach ($entries as $i => $entry) {
            if (! $cards[$i]) {
                $missing[] = $entry['label'];

                continue;
            }

            $id = $cards[$i]['id'];
            $counts[$entry['zone']][$id] = ($counts[$entry['zone']][$id] ?? 0) + $entry['count'];
        }

        return new ImportedDeck($counts, array_values(array_unique($missing)));
    }

    /**
     * @return list<array{zone: string, count: int, label: string, identifier: array}>
     */
    private function parse(string $contents): array
    {
        $lines = array_map('trim', preg_split('/\R/', $contents));
        $hasHeaders = collect($lines)->contains(fn ($line) => $this->header($line) !== null);
        $zone = 'main';
        $seenCard = false;
        $entries = [];

        foreach ($lines as $line) {
            if ($line === '') {
                // Without headers, MTGO lists put the sideboard after the first blank line.
                if (! $hasHeaders && $seenCard) {
                    $zone = 'side';
                }

                continue;
            }

            if (($header = $this->header($line)) !== null) {
                $zone = $header;

                continue;
            }

            if (! preg_match(self::LINE, $line, $match) || (int) $match[1] < 1) {
                continue;
            }

            [, $count, $name] = $match;
            $set = $match[3] ?? '';
            $number = $match[4] ?? '';

            $entries[] = [
                'zone' => str_starts_with(strtoupper($line), 'SB:') ? 'side' : $zone,
                'count' => (int) $count,
                'label' => $name,
                'identifier' => $set !== '' && $number !== ''
                    ? ['set' => strtolower($set), 'collector_number' => $number, 'name' => $name]
                    : ['name' => $name],
            ];
            $seenCard = true;
        }

        return $entries;
    }

    /**
     * Resolves identifiers, retrying by name when a set and collector number didn't match.
     *
     * @return list<array|null>
     */
    private function resolve(array $identifiers): array
    {
        $request = fn (array $identifier) => isset($identifier['set'])
            ? ['set' => $identifier['set'], 'collector_number' => $identifier['collector_number']]
            : ['name' => $identifier['name']];

        $cards = $this->magic->findByIdentifiers(array_map($request, $identifiers));

        $retry = array_keys(array_filter($cards, fn ($card, $i) => ! $card && isset($identifiers[$i]['set']), ARRAY_FILTER_USE_BOTH));

        if ($retry) {
            $byName = $this->magic->findByIdentifiers(array_map(fn ($i) => ['name' => $identifiers[$i]['name']], $retry));

            foreach ($retry as $position => $i) {
                $cards[$i] = $byName[$position];
            }
        }

        return $cards;
    }

    private function header(string $line): ?string
    {
        return match (strtolower(rtrim($line, ':'))) {
            'deck', 'main', 'maindeck', 'main deck', 'commander', 'companion' => 'main',
            'sideboard', 'side' => 'side',
            default => null,
        };
    }

    private function line(int $count, array $card): string
    {
        // Arena names double-faced cards by their front face.
        $name = explode(' // ', $card['name'] ?? 'Unknown Card')[0];

        return isset($card['set'], $card['collector_number'])
            ? sprintf('%d %s (%s) %s', $count, $name, strtoupper($card['set']), $card['collector_number'])
            : sprintf('%d %s', $count, $name);
    }
}

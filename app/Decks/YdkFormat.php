<?php

namespace App\Decks;

use App\Services\YugiohService;
use Illuminate\Support\Collection;

/**
 * The .ydk format used by EDOPro, YGO Omega and most Yu-Gi-Oh! deck tools:
 * one card passcode per line (one line per copy) under #main, #extra and !side.
 */
class YdkFormat implements DeckFormat
{
    private const HEADERS = ['#main' => 'main', '#extra' => 'extra', '!side' => 'side'];

    public function __construct(private readonly YugiohService $cards) {}

    public function extension(): string
    {
        return 'ydk';
    }

    public function importHint(): string
    {
        return 'Upload or paste a .ydk file, as exported by EDOPro, YGO Omega or YGOPRODeck.';
    }

    public function export(array $zones, Collection $cards): string
    {
        $lines = ['#created by DeckTrove'];

        foreach (self::HEADERS as $header => $zone) {
            $lines[] = $header;

            foreach ($zones[$zone] ?? [] as $id => $count) {
                array_push($lines, ...array_fill(0, $count, (string) $id));
            }
        }

        return implode("\n", $lines)."\n";
    }

    public function import(string $contents): ImportedDeck
    {
        $zone = 'main';
        $counts = [];

        foreach (preg_split('/\R/', $contents) as $line) {
            $line = trim($line);

            if (isset(self::HEADERS[strtolower($line)])) {
                $zone = self::HEADERS[strtolower($line)];
            } elseif (ctype_digit($line)) {
                // Passcodes are written without leading zeros in our data.
                $id = ltrim($line, '0') ?: '0';
                $counts[$zone][$id] = ($counts[$zone][$id] ?? 0) + 1;
            }
            // Anything else (comments like "#created by ...", blank lines) is ignored.
        }

        $known = $this->cards->findMany(collect($counts)->flatMap(fn ($zone) => array_keys($zone))->unique()->all());
        $missing = [];

        foreach ($counts as $zoneName => $zoneCounts) {
            foreach (array_keys($zoneCounts) as $id) {
                if (! $known->has($id)) {
                    $missing[] = (string) $id;
                    unset($counts[$zoneName][$id]);
                }
            }
        }

        return new ImportedDeck(array_filter($counts), array_values(array_unique($missing)));
    }
}

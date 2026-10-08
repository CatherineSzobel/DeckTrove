<?php

namespace App\Console\Commands;

use App\Models\YugiohCard;
use App\Models\YugiohPrinting;
use App\Models\YugiohSet;
use App\Services\YugiohService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Imports Yu-Gi-Oh! sets, cards and which cards were printed in which set.
 *
 * YGOPRODeck asks apps to store its data locally instead of querying the API per request,
 * so this runs once (and again to refresh the data).
 */
class ImportYugiohCards extends Command
{
    protected $signature = 'yugioh:import
        {--file= : Path to a YGOPRODeck cardinfo JSON dump (defaults to database/data/yugioh-cards.json)}
        {--sets-file= : Path to a YGOPRODeck card sets JSON dump (defaults to database/data/yugioh-sets.json)}
        {--fetch : Download the latest cards and sets from the YGOPRODeck API instead}';

    protected $description = 'Import Yu-Gi-Oh! sets and cards into the database';

    private const API = 'https://db.ygoprodeck.com/api/v7';

    public function handle(): int
    {
        // Decoding the full card dump needs far more memory than a web request is allowed.
        ini_set('memory_limit', '1G');

        $sets = $this->load('sets', '/cardsets.php', database_path('data/yugioh-sets.json'), fn ($json) => $json);
        $cards = $this->load('cards', '/cardinfo.php', database_path('data/yugioh-cards.json'), fn ($json) => $json['data'] ?? []);

        if ($sets === null || $cards === null) {
            return self::FAILURE;
        }

        DB::transaction(function () use ($sets, $cards) {
            $this->importSets($sets);
            $this->importCards($cards);
            $this->importPrintings($cards);
        });

        Cache::forget(YugiohService::FILTER_CACHE_KEY);

        return self::SUCCESS;
    }

    private function importSets(array $sets): void
    {
        $rows = array_map(fn (array $set) => YugiohSet::attributesFrom($set), $sets);

        foreach (array_chunk($rows, 500) as $chunk) {
            YugiohSet::upsert($chunk, ['name'], ['code', 'slug', 'card_count', 'released_at', 'image_url']);
        }

        $this->info('Imported '.count($sets).' sets.');
    }

    private function importCards(array $cards): void
    {
        $bar = $this->output->createProgressBar(count($cards));

        foreach (array_chunk($cards, 500) as $chunk) {
            $rows = array_map(function (array $card) {
                $attributes = YugiohCard::attributesFrom($card);
                $attributes['data'] = json_encode($attributes['data']);

                return $attributes;
            }, $chunk);

            YugiohCard::upsert($rows, ['id'], ['name', 'type', 'race', 'attribute', 'archetype', 'desc', 'data']);
            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->newLine();
        $this->info('Imported '.count($cards).' cards.');
    }

    /**
     * Rebuilds the card ↔ set links from each card's `card_sets` list.
     */
    private function importPrintings(array $cards): void
    {
        $setIds = YugiohSet::pluck('id', 'name');
        $rows = [];
        $skipped = 0;

        foreach ($cards as $card) {
            foreach ($card['card_sets'] ?? [] as $printing) {
                $setId = $setIds[$printing['set_name'] ?? ''] ?? null;

                if (! $setId) {
                    $skipped++;

                    continue;
                }

                $rows[] = [
                    'yugioh_card_id' => $card['id'],
                    'yugioh_set_id' => $setId,
                    'code' => $printing['set_code'] ?? '',
                    'rarity' => $printing['set_rarity'] ?? null,
                ];
            }
        }

        YugiohPrinting::query()->delete();

        foreach (array_chunk($rows, 1000) as $chunk) {
            YugiohPrinting::insert($chunk);
        }

        $this->info('Linked '.count($rows).' printings to their sets.'
            .($skipped ? " Skipped $skipped printings whose set is missing from the set list." : ''));
    }

    /**
     * Reads a dump from --fetch (the API), the given --file option, or the default file.
     */
    private function load(string $what, string $endpoint, string $defaultFile, callable $extract): ?array
    {
        if ($this->option('fetch')) {
            $this->info("Downloading $what from YGOPRODeck...");
            $response = Http::timeout(120)->get(self::API.$endpoint);

            if ($response->failed()) {
                $this->error("Downloading $what failed with status ".$response->status());

                return null;
            }

            return $extract($response->json());
        }

        $file = $this->option($what === 'sets' ? 'sets-file' : 'file') ?: $defaultFile;

        if (! is_file($file)) {
            $this->error("File not found: $file (use --fetch to download the latest data instead)");

            return null;
        }

        return $extract(json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR));
    }
}

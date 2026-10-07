<?php

namespace App\Console\Commands;

use App\Models\YugiohCard;
use App\Services\YugiohService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ImportYugiohCards extends Command
{
    protected $signature = 'yugioh:import
        {--file= : Path to a YGOPRODeck cardinfo JSON dump (defaults to public/json/yugioh-cards.json)}
        {--fetch : Download the latest card list from the YGOPRODeck API instead}';

    protected $description = 'Import Yu-Gi-Oh! cards into the yugioh_cards table';

    public function handle(): int
    {
        // Decoding the full dump needs far more memory than a web request is allowed.
        ini_set('memory_limit', '1G');

        $cards = $this->option('fetch') ? $this->download() : $this->readFile();

        if ($cards === null) {
            return self::FAILURE;
        }

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

        Cache::forget(YugiohService::FILTER_CACHE_KEY);
        $this->info('Imported '.count($cards).' Yu-Gi-Oh! cards.');

        return self::SUCCESS;
    }

    private function readFile(): ?array
    {
        $file = $this->option('file') ?: public_path('json/yugioh-cards.json');

        if (! is_file($file)) {
            $this->error("File not found: $file");

            return null;
        }

        return json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR)['data'] ?? [];
    }

    private function download(): ?array
    {
        $this->info('Downloading cards from YGOPRODeck...');
        $response = Http::timeout(120)->get('https://db.ygoprodeck.com/api/v7/cardinfo.php');

        if ($response->failed()) {
            $this->error('Download failed with status '.$response->status());

            return null;
        }

        return $response->json('data', []);
    }
}

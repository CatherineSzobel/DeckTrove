<?php

namespace App\Console\Commands;

use App\Cards\YugiohCardMapper;
use App\Models\YugiohCard;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Copies Yu-Gi-Oh! card images from YGOPRODeck to our media disk.
 *
 * YGOPRODeck asks apps to download images once and host them themselves (hotlinking can get the
 * server's IP blacklisted). The run is resumable: cards that are already hosted are skipped.
 */
class DownloadYugiohImages extends Command
{
    protected $signature = 'yugioh:images
        {--limit= : Only download images for this many cards}
        {--delay=100 : Milliseconds to wait between requests (YGOPRODeck allows at most 20 per second)}';

    protected $description = 'Download Yu-Gi-Oh! card images to the media disk so they are not hotlinked';

    /** Image sizes we keep: our folder name => the YGOPRODeck field. */
    private const SIZES = [
        'cards' => 'image_url',
        'cards_small' => 'image_url_small',
    ];

    public function handle(): int
    {
        $disk = Storage::disk(config('filesystems.media'));
        $delay = (int) $this->option('delay');

        $query = YugiohCard::whereNull('images_hosted_at')->orderBy('id');
        $total = min($query->count(), (int) ($this->option('limit') ?: PHP_INT_MAX));

        if ($total === 0) {
            $this->info('All card images are already hosted.');

            return self::SUCCESS;
        }

        $this->info("Downloading images for $total cards to the \"".config('filesystems.media').'" disk...');
        $bar = $this->output->createProgressBar($total);
        $failed = 0;

        foreach ($query->lazyById(100)->take($total) as $card) {
            foreach (self::SIZES as $size => $field) {
                $url = data_get($card->data, "card_images.0.$field");

                try {
                    $response = $url ? Http::timeout(30)->get($url) : null;
                } catch (ConnectionException) {
                    $response = null;
                }

                if ($response?->status() === 429) {
                    $bar->finish();
                    $this->newLine();
                    $this->error('YGOPRODeck is rate limiting us. Stopped; run the command again later to continue.');

                    return self::FAILURE;
                }

                if (! $response?->successful()) {
                    // Leave the card unmarked so the next run retries it.
                    $failed++;
                    $bar->advance();

                    continue 2;
                }

                $disk->put(YugiohCardMapper::imagePath($card->id, $size), $response->body());
                usleep($delay * 1000);
            }

            $card->update(['images_hosted_at' => now()]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done.'.($failed ? " $failed cards failed and will be retried on the next run." : ''));

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NoticeScraper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchNotices extends Command
{
    protected $signature = 'notices:fetch';
    protected $description = 'Fetch latest notices from BUBT website and cache them';

    public function handle(NoticeScraper $scraper)
    {
        $this->info('Fetching notices from BUBT...');

        try {
            $notices = $scraper->getAll();

            // Cache for quick API use
            Cache::put('bubt:notices', $notices, now()->addMinutes(5));

            $this->info('✅ Notices updated successfully.');
        } catch (\Throwable $e) {
            Log::error("Notice fetch failed: " . $e->getMessage());
            $this->error('❌ Failed to fetch notices.');
        }

        return Command::SUCCESS;
    }
}

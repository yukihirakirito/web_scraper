<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Scraper\ESPNScraper;
use App\Http\Scraper\TheSunScraper;
use App\Http\Scraper\MarcaScraper;
use App\Http\Scraper\MundoDeportivoScraper;
use App\Http\Scraper\NinetyMinutesScraper;
use Illuminate\Support\Facades\Log;

class ScrapeNewsCommand extends Command
{
    protected $signature = 'scrape:news';
    protected $description = 'Fetch latest football news from various sources';

    public function handle()
    {
        Log::info('Starting news scraping...');
        
        $scrapers = [
            new ESPNScraper(),
            new MarcaScraper(),
            new TheSunScraper(),
            new NinetyMinutesScraper(),
            new MundoDeportivoScraper(),
        ];

        foreach ($scrapers as $scraper) {
            try {
                $scraper->fetchArticles();
                Log::info(get_class($scraper) . ' completed.');
            } catch (\Throwable $th) {
                Log::error('Error in ' . get_class($scraper) . ': ' . $th->getMessage());
            }
        }

        Log::info('News scraping completed.');
    }
}

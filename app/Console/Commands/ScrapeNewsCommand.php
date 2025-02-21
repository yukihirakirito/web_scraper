<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Factories\ScraperFactory;

class ScrapeNewsCommand extends Command
{
    protected $signature = 'scrape:news';
    protected $description = 'Fetch latest football news from various sources';

    protected array $sources = [
        'espn',
        'marca',
        'thesun',
        '90min',
        'mundodeportivo',
    ];

    public function handle()
    {
        Log::info('Starting news scraping...');

        foreach ($this->sources as $source) {
            try {
                $scraper = ScraperFactory::create($source);
                $scraper->fetchArticles();
                Log::info("$source scraper completed.");
            } catch (\Throwable $th) {
                Log::error("Error in $source scraper: " . $th->getMessage());
            }
        }

        Log::info('News scraping completed.');
    }
}

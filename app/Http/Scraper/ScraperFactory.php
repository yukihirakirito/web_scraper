<?php

namespace App\Factories;

use App\Http\Scraper\ESPNScraper;
use App\Http\Scraper\MarcaScraper;
use App\Http\Scraper\MundoDeportivoScraper;
use App\Http\Scraper\NinetyMinutesScraper;
use App\Http\Scraper\TheSunScraper;
use InvalidArgumentException;

class ScraperFactory
{
    public static function create(string $type)
    {
        switch (strtolower($type)) {
            case 'espn':
                return new ESPNScraper();
            case 'marca':
                return new MarcaScraper();
            case 'mundodeportivo':
                return new MundoDeportivoScraper();
            case 'thesun':
                return new TheSunScraper();
            case '90min':
                return new NinetyMinutesScraper();
            default:
                throw new InvalidArgumentException("Unknown scraper type: $type");
        }
    }
}

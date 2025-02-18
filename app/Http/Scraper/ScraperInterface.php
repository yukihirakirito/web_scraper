<?php
namespace App\Http\Scraper;

interface ScraperInterface
{
    public function fetchArticles(): void;
}

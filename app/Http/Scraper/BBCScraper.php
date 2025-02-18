<?php
namespace App\Http\Scraper;

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;

class BBCScraper implements ScraperInterface
{
    private Client $client;
    private string $url = 'https://www.bbc.com/sport/football';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function fetchArticles(): void
    {
        $response = $this->client->get($this->url);
        $html = (string) $response->getBody();

        $crawler = new Crawler($html);
        $articles = [];

        $crawler->filter('a.gs-c-promo-heading')->each(function ($node) use (&$articles) {
            $title = trim($node->text());
            $url = $node->attr('href');
            if ($url && str_starts_with($url, '/')) {
                $url = 'https://www.bbc.com' . $url;
            }
            if (!empty($title) && !empty($url)) {
                $articles[] = ['title' => $title, 'url' => $url];
            }
        });

        // return $articles;
    }
}
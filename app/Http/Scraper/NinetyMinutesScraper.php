<?php
namespace App\Http\Scraper;

use GuzzleHttp\Client;
use App\Http\HttpClient;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class NinetyMinutesScraper extends BaseScraper implements ScraperInterface
{
    private Client $client;
    public function __construct()
    {
        $this->client = HttpClient::getInstance();
    }

    public function fetchArticles(): void
    {
        try {
            $url = env('90MIN');
            $response = $this->client->get($url, [
                'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36']
            ]);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $articles = [];
        
            $crawler->filter('article a')->each(function ($node) use (&$articles, $url) {
                $title = trim($node->text());
                $link = $node->attr('href');
                if ($link && str_starts_with($link, '/')) {
                    $link = $url . $link;
                }
                if (!empty($title) && !empty($link)) {
                    $articles[] = ['title' => $title, 'url' => $link];
                }
            });
            // Lọc bài báo về bóng đá
            $soccerArticles = $this->filterSoccerArticles($articles);
            // Đẩy dữ liệu lên DB
            $this->saveArticles($soccerArticles, '90MIN');
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }
}
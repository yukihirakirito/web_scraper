<?php
namespace App\Http\Scraper;

use GuzzleHttp\Client;
use App\Http\HttpClient;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class TheSunScraper extends BaseScraper implements ScraperInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = HttpClient::getInstance();
    }

    public function fetchArticles(): void
    {
        try {
            $url = env('THE_SUN');
            $originalUrl = env('THE_SUN_FOOTBALL');
            $response = $this->client->get($url, [
                'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36']
            ]);
            $html = (string) $response->getBody();

            $crawler = new Crawler($html);
            $articles = [];

            $crawler->filter('.teaser-item a')->each(function ($node) use (&$articles, $originalUrl) {
                $title = trim($node->text());
                $url = $node->attr('href');
                if ($url && str_starts_with($url, '/')) {
                    $url = $originalUrl . $url;
                }
                if (!empty($title) && !empty($url)) {
                    $articles[] = $this->normalizeArticle(['title' => $title, 'url' => $url]);
                }
            });

            // Lọc bài báo về bóng đá
            $soccerArticles = $this->filterSoccerArticles($articles);
            // Đẩy dữ liệu lên DB
            $this->saveArticles($soccerArticles, 'The Sun');
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }
}

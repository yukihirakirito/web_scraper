<?php
namespace App\Http\Scraper;

use GuzzleHttp\Client;
use App\Http\HttpClient;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ESPNScraper extends BaseScraper implements ScraperInterface 
{
    private Client $client;

    public function __construct()
    {
        $this->client = HttpClient::getInstance();
    }

    public function fetchArticles(): void
    {
        try {
            $url = env('ESPN_SOCCER');
            $originalUrl = env('ESPN');
            // Lấy nội dung trang ESPN Soccer
            $response = $this->client->get($url, [
                'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36']
            ]);
            $html = (string) $response->getBody();
    
            $crawler = new Crawler($html);
            $articles = [];
    
            $crawler->filter('article a')->each(function ($node) use (&$articles, $originalUrl) {
                $title = trim($node->text());
                $link = $node->attr('href');
                if ($link && str_starts_with($link, '/')) {
                    $link = $originalUrl . $link;
                }
                if (!empty($title) && !empty($link)) {
                    // Lưu bài báo vào mảng $articles
                    $articles[] = $this->normalizeArticle(['title' => $title, 'url' => $link]);
                }
            });
            // Lọc bài báo về bóng đá
            $soccerArticles = $this->filterSoccerArticles($articles);
            // Đẩy dữ liệu lên DB
            $this->saveArticles($soccerArticles, 'ESPN');
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }
}

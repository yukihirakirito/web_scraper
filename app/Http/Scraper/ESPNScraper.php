<?php
namespace App\Http\Scraper;

use Symfony\Component\DomCrawler\Crawler;
use App\Http\HttpClient;
use App\Models\Article;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ESPNScraper implements ScraperInterface
{
    private Client $client;
    private string $espn_soccer_url = 'https://www.espn.com/soccer/';
    private string $espn_url = 'https://www.espn.com/';

    public function __construct()
    {
        $this->client = HttpClient::getInstance();
    }

    public function fetchArticles(): void
    {
        try {
            // Lấy nội dung trang ESPN Soccer
            $response = $this->client->get($this->espn_soccer_url);
            $html = (string) $response->getBody();
    
            $crawler = new Crawler($html);
            $articles = [];
    
            $crawler->filter('article a')->each(function ($node) use (&$articles) {
                $title = trim($node->text());
                $url = $node->attr('href');
                if ($url && str_starts_with($url, '/')) {
                    $url = $this->espn_url . $url;
                }
                if (!empty($title) && !empty($url)) {
                    // Lưu bài báo vào mảng $articles
                    $articles[] = $this->normalizeArticle(['title' => $title, 'url' => $url]);
                }
            });
            // Mảng để lưu các bài báo ESPN liên quan đến bóng đá
            $soccerArticles = [];
            // Load cấu hình từ tệp keywords.php    
            $group_keywords = config('keywords', []);
            if (empty($group_keywords)) {
                Log::warning('Keywords config is empty or missing');
            }
            // Lọc các bài báo liên quan đến bóng đá (sử dụng từ khóa "soccer", "football", "LaLiga", v.v.)
            foreach ($articles as $article) {
                $raw = $article;
                $raw['keywords'] = [];
                foreach ($group_keywords as $group) {
                    foreach ($group as $keyword) {
                        // Kiểm tra xem tiêu đề có chứa từ khóa liên quan đến bóng đá
                        $check = $this->isSoccerArticle($raw, $keyword);
                        if ($check && !in_array($raw, $soccerArticles)) {
                            $raw['keywords'][] = $keyword;
                        }
                    }
                }
                if (!empty($raw['keywords'])) {
                    $soccerArticles[] = $raw;
                }
            }
            DB::beginTransaction();
            // Lưu các bài báo vào cơ sở dữ liệu
            Article::insert($soccerArticles);
            DB::commit();
            Log::info('Inserted ' . count($soccerArticles) . ' articles');
        } catch (\Throwable $th) {
            // Rollback nếu có lỗi xảy ra
            DB::rollBack();
            Log::error($th);
            // return [];
        }
    }

    private function normalizeArticle(array $article): array
    {
        $article['title'] = trim($article['title']);
        $article['url'] = $article['url'];
        return $article;
    }

    private function isSoccerArticle(array $article, string $keyword): bool
    {
        $title = $article['title'];
        $url = $article['url'];
        $checkTitle = $this->isSoccerArticleTitle($title, $keyword);
        $checkUrl = $this->isSoccerArticleUrl($url, $keyword);
        return $checkTitle || $checkUrl;
    }

    private function isSoccerArticleUrl(string $url, string  $keyword): bool
    {
        // Chỉ chấp nhận các url từ page của ESPN, không lấy từ các phương tiện khác như tiktok, youtube, v.v.
        if (strpos($url, $this->espn_url) === false) {
            return false;
        }
        // Kiểm tra xem url có chứa từ khóa liên quan đến bóng đá
        if (strpos($url, strtolower($keyword)) === false) {
            return false;
        }
        return true;
    }

    private function isSoccerArticleTitle(string $title, string $keyword): bool
    {
        // Kiểm tra xem tiêu đề có chứa từ khóa liên quan đến bóng đá
        return strpos(strtolower($title), strtolower($keyword)) !== false;
    }
}

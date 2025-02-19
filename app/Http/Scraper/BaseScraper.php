<?php
namespace App\Http\Scraper;

use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseScraper
{
    abstract public function fetchArticles(): void;

    protected function normalizeArticle(array $article): array
    {
        $article['title'] = trim($article['title']);
        $article['url'] = $article['url'];
        return $article;
    }

    protected function filterSoccerArticles(array $articles): array {
        try {
        // Mảng để lưu các bài báo liên quan đến bóng đá
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
        return $soccerArticles;
        } catch (\Throwable $th) {
            Log::error($th);
            return [];
        }
    }

    protected function isSoccerArticle(array $article, string $keyword): bool
    {
        $title = $article['title'];
        $url = $article['url'];
        $checkTitle = $this->isSoccerArticleTitle($title, $keyword);
        $checkUrl = $this->isSoccerArticleUrl($url, $keyword);
        return $checkTitle || $checkUrl;
    }

    protected function isSoccerArticleTitle(string $title, string $keyword): bool
    {
        // Kiểm tra xem tiêu đề có chứa từ khóa liên quan đến bóng đá
        return strpos(strtolower($title), strtolower($keyword)) !== false;
    }

    protected function isSoccerArticleUrl(string $url, string  $keyword): bool
    {
        // // Chỉ chấp nhận các url từ page của ESPN, không lấy từ các phương tiện khác như tiktok, youtube, v.v.
        // if (strpos($url, $this->espn_url) === false) {
        //     return false;
        // }
        // Kiểm tra xem url có chứa từ khóa liên quan đến bóng đá
        if (strpos($url, strtolower($keyword)) === false) {
            return false;
        }
        return true;
    }

    protected function saveArticles(array $articles, string $source): void
    {
        DB::beginTransaction();
        try {
            foreach ($articles as $articleData) {
                Article::create([
                    'source' => $source,
                    'title' => $articleData['title'],
                    'url' => $articleData['url'],
                    'keywords' => json_encode($articleData['keywords'] ?? []),
                ]);
            }
            DB::commit();
            Log::info('Inserted ' . count($articles) . ' articles from ' . $source);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
        }
    }
}

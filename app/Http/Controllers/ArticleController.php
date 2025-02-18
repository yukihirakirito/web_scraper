<?php
namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $articles = Article::when($search, function ($query) use ($search) {
            return $query->where('title', 'LIKE', "%$search%");
        })->orderBy('created_at', 'desc')->paginate(9);
        return view('articles.index', compact('articles'));
    }
}

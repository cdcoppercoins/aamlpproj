<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));

        $articlesQuery = Article::query()->publishedOrdered();

        if ($query !== '') {
            $articlesQuery->matchingKeyword($query);
        }

        return view('articles.index', [
            'articles' => $articlesQuery->get(),
            'query' => $query,
        ]);
    }

    public function show(string $slug): View
    {
        $article = Article::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with('images')
            ->firstOrFail();

        return view('articles.show', [
            'article' => $article,
        ]);
    }
}

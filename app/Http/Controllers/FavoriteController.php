<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use function auth;

class FavoriteController extends Controller
{
    public function favorite(Article $article): ArticleResource
    {
        $article->favoritedBy()->attach(auth()->id());

        return new ArticleResource($article);
    }

    public function unfavorite(Article $article): ArticleResource
    {
        $article->favoritedBy()->detach(auth()->id());

        return new ArticleResource($article);
    }
}

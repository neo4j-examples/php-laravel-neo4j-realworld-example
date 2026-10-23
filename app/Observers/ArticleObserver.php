<?php

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class ArticleObserver
{
    public function created(Article $comment): void
    {
        $comment->author()->attach(Auth::id());
    }

    public function deleting(Article $comment): void
    {
        $comment->author()->detach();
    }
}

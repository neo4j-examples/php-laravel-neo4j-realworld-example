<?php

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Facades\Auth;

class ArticleObserver
{
    public function created(Article $comment): void
    {
        $comment->author()->associate(Auth::user());
        $comment->tags()->detach($comment->tags);
    }

    public function deleting(Article $comment): void
    {
        $comment->author()->dissociate();
    }
}

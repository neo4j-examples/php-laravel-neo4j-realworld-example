<?php

namespace App\Observers;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        $comment->author()->associate(Auth::user());
    }

    public function deleting(Comment $comment): void
    {
        $comment->author()->dissociate();
    }
}

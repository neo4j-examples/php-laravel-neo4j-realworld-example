<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ArticlePolicy
{
    use HandlesAuthorization;

    public function update(User $user, Article $article): Response
    {
        if ($user->getAuthIdentifier() === $article->author->getAuthIdentifier()) {
            return $this->allow();
        }

        return $this->deny('You are not the author of this post.');
    }

    public function delete(User $user, Article $article): Response
    {
        return $this->update($user, $article);
    }
}

<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Comment $article): Response
    {
        // only the author can update its own comment.
        if ($user->getAuthIdentifier() === $article->author->getAuthIdentifier()) {
            return $this->allow();
        }

        return $this->deny('You are not the author of this comment.');
    }

    public function delete(User $user, Comment $comment): Response
    {
        $id = $user->getAuthIdentifier();
        // The author of the article MAY also delete a comment
        if ($id === $comment->author->getAuthIdentifier() ||
            $id === $comment->article->author->getAuthIdentifier()) {
            return $this->allow();
        }

        return $this->deny('You are not the author of this comment or its corresponding article.');
    }
}

<?php

namespace App\Presenters;

use App\Models\Comment;
use App\Models\User;
use function optional;
use const DATE_ATOM;

class CommentJSONPresenter
{
    public function present(Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'createdAt' => $comment->createdAt->format(DATE_ATOM),
            'updatedAt' => $comment->updatedAt?->format(DATE_ATOM),
            'body' => $comment->body,
        ];
    }

    /**
     * @param list<array{comment: Comment, author: User, following: bool}> $comments
     * @return array
     */
    public function presentCommentsOnArticle(array $comments): array
    {
        $userPresenter = new UserJSONPresenter();
        $tbr = [];
        foreach ($comments as $comment) {
            $tbr[] = array_merge(
                $this->present($comment['comment']),
                ['author' => $userPresenter->presentAsProfile($comment['author'], $comment['following'])]
            );
        }

        return $tbr;
    }
}

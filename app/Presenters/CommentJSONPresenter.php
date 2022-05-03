<?php

namespace App\Presenters;

use App\Models\Comment;
use App\Models\User;
use const DATE_ATOM;

class CommentJSONPresenter
{
    public function __construct(private readonly UserJSONPresenter $userPresenter)
    {
    }

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
     * @param array<Comment> $comments
     * @param array<string, User> $authorMap
     * @param array<string, bool> $followingMap
     */
    public function presentCommentsOnArticle(array $comments, array $authorMap, array $followingMap): array
    {
        $tbr = [];
        foreach ($comments as $comment) {
            $author = $authorMap[$comment->id];
            $present = $this->presentFullComment($author, $comment, $followingMap[$author->username]);

            $tbr[] = $present;
        }

        return $tbr;
    }

    public function presentFullComment(User $author, Comment $comment, bool $following): array
    {
        $present = $this->present($comment);
        $present['author'] = $this->userPresenter->presentAsProfile($author, $following);

        return $present;
    }
}

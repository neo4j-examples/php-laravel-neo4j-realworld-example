<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use App\Presenters\CommentJSONPresenter;
use App\Repositories\CommentRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function auth;
use function response;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentRepository $repository,
        private readonly UserRepository $userRepository,
        private readonly CommentJSONPresenter $presenter
    ) {
    }

    public function getComments(Request $request, string $slug): JsonResponse
    {
        $comments = $this->repository->getComments($slug);
        $commentIds = array_map(static fn (Comment $c) => $c->id, $comments);
        $authorMap = $this->userRepository->getCommentAuthors($commentIds);
        $authorUsernames = array_map(static fn (User $u) => $u->username, $authorMap);
        $followingMap = [];
        if (auth()->id()) {
            $followingMap = $this->userRepository->following(auth()->id(), $authorUsernames);
        }

        return response()->json(['comments' => $this->presenter->presentCommentsOnArticle($comments, $authorMap, $followingMap)]);
    }

    public function comment(Request $request, string $slug): JsonResponse
    {
        $comment = $this->repository->comment($slug, $request->json('comment')['body'], auth()->id());

        $user = $this->userRepository->findByUsername(auth()->id());

        return response()->json(['comment' => $this->presenter->presentFullComment($user, $comment, false)]);
    }

    public function uncomment(Request $request, string $slug, int $id): JsonResponse
    {
        $this->repository->uncomment($slug, auth()->id(), $id);

        return response()->json();
    }
}

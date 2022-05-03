<?php

namespace App\Http\Controllers;

use App\Presenters\CommentJSONPresenter;
use App\Repositories\CommentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function auth;
use function response;

class CommentController extends Controller
{
    public function __construct(private readonly CommentRepository $repository, private readonly CommentJSONPresenter $presenter)
    {
    }

    public function getComments(Request $request, string $slug): JsonResponse
    {
        $comments = $this->repository->getComments($slug, auth()->id());

        return response()->json(['comments' => $this->presenter->presentCommentsOnArticle($comments)]);
    }

    public function comment(Request $request, string $slug): JsonResponse
    {
        $comments = $this->repository->comment($slug, $request->json('comment')['body'], auth()->id());

        return response()->json(['comments' => $this->presenter->presentCommentsOnArticle($comments)]);
    }

    public function uncomment(Request $request, string $slug, int $id): JsonResponse
    {
        $this->repository->uncomment($slug, auth()->id(), $id);

        return response()->json();
    }
}

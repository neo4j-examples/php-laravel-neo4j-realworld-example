<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use function auth;
use function response;

class CommentController extends Controller
{
    public function getComments(Article $article): ResourceCollection
    {
        return CommentResource::collection($article->comments);
    }

    public function comment(Request $request, Article $article): JsonResponse
    {
        $data = $request->json('comment');

        /** @var Comment $comment */
        $comment = Comment::query()->create(Arr::only($data, 'body'));

        $comment->article()->associate($article);

        return (new CommentResource($comment))
            ->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function uncomment(Article $slug, Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->article()->dissociate();

        return response()->json();
    }
}

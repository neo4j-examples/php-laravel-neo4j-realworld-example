<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use function response;

class CommentController extends Controller
{
    public function getComments(Article $article): CommentCollection
    {
        return new CommentCollection($article->comments->sortBy('id')->values());
    }

    public function comment(Request $request, Article $article): JsonResponse
    {
        $request->validate([
            'comment.body' => 'required|string|max:10000'
        ]);

        $data = $request->json('comment');

        $comment = Comment::query()->create(Arr::only($data, 'body'));

        $comment->article()->attach($article->id);

        return (new CommentResource($comment))
            ->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function uncomment(Article $article, Comment $comment): Response
    {
        if ($comment->article?->id !== $article->id) {
            throw (new ModelNotFoundException())->setModel(Comment::class);
        }

        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }
}

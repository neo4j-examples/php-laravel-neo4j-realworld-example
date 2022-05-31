<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use function auth;
use function response;

class ArticleController extends Controller
{
    public function listArticles(Request $request): ResourceCollection
    {
        $perPage = $request->query('limit', 20);
        $page = (int)($request->query('offset', 0) / $perPage);
        $pagination = User::query()->paginate(perPage: $perPage, page: $page);

        return ArticleResource::collection($pagination);
    }

    public function queryArticles(Request $request): ResourceCollection
    {
        $query = Article::query();

        if ($request->has('tag')) {
            $query->whereRelation('tags', 'name', $request->get('tag'));
        }

        if ($request->has('author')) {
            $query->whereRelation('tags', 'username', $request->get('author'));
        }

        if ($request->has('favorited')) {
            $query->whereRelation('favoritedBy', 'username', $request->get('favorited'));
        }

        $perPage = $request->query('limit', 20);
        $page = (int)($request->query('offset', 0) / $perPage);

        $pagination = $query->paginate(perPage: $perPage, page: $page);

        return ArticleResource::collection($pagination);
    }

    public function getArticle(Article $article): ArticleResource
    {
        return new ArticleResource($article);
    }

    public function createArticle(Request $request): JsonResponse
    {
        $params = $request->json('article');

        /** @var Article $model */
        $model = Article::query()->create(Arr::only($params, ['title', 'description', 'body']));

        $model->author()->associate(auth()->id());
        // updateOrCreate uses MERGE under the hood.
        $tags = collect($params['tagList'])
            ->map(static fn(string $x) => Tag::query()->updateOrCreate(['name' => $x]));

        $model->tags()->saveMany($tags);

        return (new ArticleResource($model))
            ->toResponse($request)
            ->setStatusCode(201);
    }

    public function deleteArticle(Article $article): JsonResponse
    {
        $this->authorize('delete', $article);

        Article::destroy($article->slug);

        return response()->json();
    }

    public function updateArticle(Request $request, Article $article): ArticleResource
    {
        $this->authorize('update', $article);

        $parameters = $request->json('article');

        $article->update(Arr::only($parameters, ['description', 'body', 'title']));

        return new ArticleResource($article);
    }
}

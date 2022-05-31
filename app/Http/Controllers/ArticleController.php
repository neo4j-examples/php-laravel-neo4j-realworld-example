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
        $tags = collect($params['tagList'])->map(static fn(string $x) => new Tag(['name' => $x]));
        $model->tags()->saveMany($tags);

        return (new ArticleResource($model))
            ->toResponse($request)
            ->setStatusCode(201);
    }

    public function deleteArticle(Request $request, string $slug): JsonResponse
    {
        Gate::authorize('change-article', $slug);

        Article::destroy($slug);

        return response()->json();
    }

    public function updateArticle(Request $request, Article $article): ArticleResource
    {
        // TODO - make sure only authors can change their own article
        $parameters = $request->json('article');

        $article->update(Arr::only($parameters, ['description', 'body', 'title']));

        return new ArticleResource($article);
    }
}

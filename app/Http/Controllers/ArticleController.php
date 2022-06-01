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
        $request->validate([
            'limit' => 'optional|numeric|min:1|max:200',
            'offset' => 'optional|numeric|min:0'
        ]);

        $perPage = $request->query('limit', 20);
        $page = (int)($request->query('offset', 0) / $perPage);
        $pagination = User::query()->paginate(perPage: $perPage, page: $page);

        return ArticleResource::collection($pagination);
    }

    public function queryArticles(Request $request): ResourceCollection
    {
        $request->validate([
            'limit' => 'optional|numeric|min:1|max:200',
            'offset' => 'optional|numeric|min:0'
        ]);

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
        $request->validate([
            'article.title' => 'required|min:5|max:150',
            'article.description' => 'required|string|min:0|max:255',
            'article.body' => 'required|string|min:5|max:50000',
            'article.tagList.*' => 'nullable|string|min:3|max:50000'
        ]);

        $params = $request->json('article');

        /** @var Article $model */
        $model = Article::query()->create($params->all());

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
        $request->validate([
            'article.title' => 'optional|string|min:5|max:150',
            'article.description' => 'optional|string|min:0|max:255',
            'article.body' => 'optional|string|min:5|max:50000'
        ]);

        $this->authorize('update', $article);

        $parameters = $request->json('article')?->all();

        $article->update(Arr::only($parameters, ['description', 'body', 'title']));

        return new ArticleResource($article);
    }
}

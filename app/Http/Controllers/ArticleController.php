<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use function intdiv;
use function response;

class ArticleController extends Controller
{
    public function listArticles(Request $request): ArticleCollection
    {
        $request->validate([
            'limit' => 'sometimes|numeric|min:1|max:200',
            'offset' => 'sometimes|numeric|min:0'
        ]);

        $perPage = (int) $request->query('limit', 20);
        $page = intdiv((int) $request->query('offset', 0), $perPage) + 1;


        $pagination = Article::query()
            ->with(['tags', 'author', 'favoritedBy', 'favoritedBySelf'])
            ->paginate(perPage: $perPage, page: $page);

        return new ArticleCollection($pagination);
    }

    public function queryArticles(Request $request): ArticleCollection
    {
        $request->validate([
            'limit' => 'sometimes|numeric|min:1|max:200',
            'offset' => 'sometimes|numeric|min:0'
        ]);

        $query = Article::query()->with(['tags', 'author', 'favoritedBy', 'favoritedBySelf']);

        if ($request->has('tag')) {
            $query->matchRelationship('<TAGGED', 'Tag', 'tagged', 'tag', static fn ($q) => $q->where('name', $request->get('tag')));
        }

        if ($request->has('author')) {
            $query->matchRelationship('<AUTHORED', 'User', 'authored', 'author', static fn ($q) => $q->where('username', $request->get('author')));
        }

        if ($request->has('favorited')) {
            $query->matchRelationship('<FAVORITED', 'User', 'favorite', 'favoriter', static fn ($q) => $q->where('username', $request->get('favorited')));
        }

        $perPage = (int) $request->query('limit', 20);
        $page = intdiv((int) $request->query('offset', 0), $perPage) + 1;

        $pagination = $query->paginate(perPage: $perPage, page: $page);

        return new ArticleCollection($pagination);
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
        $model = Article::query()->create($params);

        $tags = collect($params['tagList'] ?? [])
            ->map(static fn(string $x) => Tag::query()->firstOrCreate(['name' => $x]));

        $model->tags()->attach($tags->pluck('id')->all());

        return (new ArticleResource($model))
            ->toResponse($request)
            ->setStatusCode(201);
    }

    public function deleteArticle(Article $article): JsonResponse
    {
        $this->authorize('delete', $article);

        $article->delete();

        return response()->json();
    }

    public function updateArticle(Request $request, Article $article): ArticleResource
    {
        $request->validate([
            'article.title' => 'sometimes|string|min:5|max:150',
            'article.description' => 'sometimes|string|min:0|max:255',
            'article.body' => 'sometimes|string|min:5|max:50000'
        ]);

        $this->authorize('update', $article);

        $parameters = $request->json('article', []);

        $article->update(Arr::only($parameters, ['description', 'body', 'title']));

        return new ArticleResource($article);
    }
}

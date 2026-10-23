<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use function array_key_exists;
use function response;

class ArticleController extends Controller
{
    public function feed(Request $request): ArticleCollection
    {
        /** @var User $user */
        $user = Auth::user();
        $following = $user->following()->pluck('username')->all();

        $query = $this->articleQuery()
            ->matchRelationship('<AUTHORED', 'User', 'authored', 'author', static fn ($q) => $q->whereIn('username', $following));

        return $this->paginate($request, $query);
    }

    public function queryArticles(Request $request): ArticleCollection
    {
        $query = $this->articleQuery();

        if ($request->has('tag')) {
            $query->matchRelationship('<TAGGED', 'Tag', 'tagged', 'tag', static fn ($q) => $q->where('name', $request->get('tag')));
        }

        if ($request->has('author')) {
            $query->matchRelationship('<AUTHORED', 'User', 'authored', 'author', static fn ($q) => $q->where('username', $request->get('author')));
        }

        if ($request->has('favorited')) {
            $query->matchRelationship('<FAVORITED', 'User', 'favorite', 'favoriter', static fn ($q) => $q->where('username', $request->get('favorited')));
        }

        return $this->paginate($request, $query);
    }

    public function getArticle(Article $article): ArticleResource
    {
        return new ArticleResource($article);
    }

    public function createArticle(Request $request): JsonResponse
    {
        $request->validate([
            'article.title' => 'required|string|max:150',
            'article.description' => 'required|string|max:255',
            'article.body' => 'required|string|max:50000',
            'article.tagList' => 'sometimes|array',
            'article.tagList.*' => 'string|min:1|max:255',
        ]);

        $params = $request->json('article');

        /** @var Article $model */
        $model = Article::query()->create(Arr::only($params, ['title', 'description', 'body']));

        $model->tags()->attach($this->tagIds($params['tagList'] ?? []));

        return (new ArticleResource($model))
            ->toResponse($request)
            ->setStatusCode(201);
    }

    public function deleteArticle(Article $article): Response
    {
        $this->authorize('delete', $article);

        $article->comments->each->delete();
        $article->delete();

        return response()->noContent();
    }

    public function updateArticle(Request $request, Article $article): ArticleResource
    {
        $this->authorize('update', $article);

        $request->validate([
            'article.title' => 'sometimes|required|string|max:150',
            'article.description' => 'sometimes|required|string|max:255',
            'article.body' => 'sometimes|required|string|max:50000',
            'article.tagList' => 'sometimes|array',
            'article.tagList.*' => 'string|min:1|max:255',
        ]);

        $parameters = $request->json('article', []);

        $article->update(Arr::only($parameters, ['description', 'body', 'title']));

        if (array_key_exists('tagList', $parameters)) {
            $article->tags()->sync($this->tagIds($parameters['tagList']));
            $article->unsetRelation('tags');
        }

        return new ArticleResource($article);
    }

    private function articleQuery(): Builder
    {
        return Article::query()->with(['tags', 'author', 'favoritedBy', 'favoritedBySelf']);
    }

    private function paginate(Request $request, Builder $query): ArticleCollection
    {
        $request->validate([
            'limit' => 'sometimes|integer|min:1|max:200',
            'offset' => 'sometimes|integer|min:0'
        ]);

        $limit = (int) $request->query('limit', 20);
        $offset = (int) $request->query('offset', 0);

        $total = (clone $query)->count();
        $articles = $query->orderByDesc('createdAt')->skip($offset)->take($limit)->get();

        return new ArticleCollection(new LengthAwarePaginator($articles, $total, $limit));
    }

    /**
     * @param list<string> $names
     * @return list<string>
     */
    private function tagIds(array $names): array
    {
        return collect($names)
            ->unique()
            ->map(static fn (string $name) => Tag::query()->firstOrCreate(['name' => $name])->id)
            ->values()
            ->all();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\ArticleModel;
use App\Models\Tag;
use App\Models\TagModel;
use App\Models\User;
use App\Presenters\ArticleJSONPresenter;
use App\Repositories\ArticleRepository;
use App\Repositories\FavoriteRepository;
use App\Repositories\TagsRepository;
use App\Repositories\UserRepository;
use App\SlugGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use function array_map;
use function auth;
use function response;

class ArticleController extends Controller
{
    public function __construct(
        private readonly SlugGenerator $slugGenerator,
        private readonly ArticleRepository $repository,
        private readonly UserRepository $userRepository,
        private readonly TagsRepository $tagsRepository,
        private readonly FavoriteRepository $favoriteRepository,
        private readonly ArticleJSONPresenter $presenter
    )
    {
    }

    public function listArticles(Request $request): JsonResponse
    {
        $articles = $this->repository->listArticles(
            $request->query('tag', null),
            $request->query('author', null),
            $request->query('favorited', null),
            (int) $request->query('limit', '20'),
            (int) $request->query('offset', '0')
        );
        $articleCount = $this->repository->articlesCount();
        $slugs = array_map(static fn (Article $a) => $a->slug, $articles);

        $tags = $this->tagsRepository->getTags($slugs);
        $authors = $this->userRepository->getAuthorFromArticle($slugs);
        $authorUserNames = array_map(static fn (User $u) => $u->username, $authors);
        $username = auth()->id();
        $followingMap = [];
        if ($username) {
            $followingMap = $this->userRepository->following($username, $authorUserNames);
        }
        $favoriteCount = $this->favoriteRepository->countFavorites($slugs);
        $favoritedMap = [];
        if ($username) {
            $favoritedMap = $this->favoriteRepository->favorited($username, $slugs);
        }

        return response()->json($this->presenter->presentFullArticles($articles, $articleCount, $tags, $authors, $favoriteCount, $favoritedMap, $followingMap));
    }

    public function getArticle(ArticleModel $article): ArticleResource
    {
//        $tags = $this->tagsRepository->getTags([$article->slug])[$article->slug] ?? [];
//        $author = $this->userRepository->getAuthorFromArticle([$article->slug])[$article->slug];
//
//        $username = auth()->id();
//        if ($username === null) {
//            $following = false;
//        } else {
//            $following = $this->userRepository->following($username, [$author->username])[$author->username] ?? false;
//        }
//        $favoriteCount = $this->favoriteRepository->countFavorites([$article->slug])[$article->slug];
//
//        if ($username === null) {
//            $favorited = false;
//        } else {
//            $favorited = $this->favoriteRepository->favorited($username, [$article->slug])[$article->slug] ?? false;
//        }

        return new ArticleResource($article);
    }

    public function createArticle(Request $request): JsonResponse
    {
        $params = $request->json('article');

        /** @var ArticleModel $model */
        $model = ArticleModel::query()->create($params);
        $model->author()->associate(auth());
        $tags = collect($params['tagList'])->map(static fn(string $x) => new TagModel(['name' => $x]));
        $model->tags()->saveMany($tags);

        return (new ArticleResource($model))
            ->toResponse($request)
            ->setStatusCode(201);
    }

    public function deleteArticle(Request $request, string $slug): JsonResponse
    {
        Gate::authorize('change-article', $slug);

        $this->repository->deleteArticle($slug);

        return response()->json();
    }

    public function updateArticle(Request $request, string $slug): JsonResponse
    {
        // TODO - make sure only authors can change their own article
        $parameters = $request->json('article');
        $this->repository->updateArticle($slug, $parameters['description'] ?? null, $parameters['body'] ?? null, $parameters['title'] ?? null);

        return $this->getArticle($request, $slug)->setStatusCode(200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use App\Repositories\FavoriteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function auth;

class FavoriteController extends Controller
{
    public function __construct(private readonly FavoriteRepository $repository, private readonly ArticleController $articleController)
    {
    }

    public function favorite(Request $request, string $slug): JsonResponse
    {
        $this->repository->favorite(auth()->id(), $slug);

        return $this->articleController->getArticle($request, $slug);
    }

    public function unfavorite(Request $request, string $slug): JsonResponse
    {
        $this->repository->favorite(auth()->id(), $slug);

        return $this->articleController->getArticle($request, $slug);
    }
}

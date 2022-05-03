<?php

namespace App\Http\Controllers;

use App\Presenters\UserJSONPresenter;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function auth;
use function response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly UserJSONPresenter $presenter
    ) {
    }

    public function getProfile(Request $request, string $username): JsonResponse
    {
        $user = $this->repository->findByUsername($username);
        $following = $this->repository->following(auth()->id() ?? '', $user->username);

        return response()->json([
            'profile' => $this->presenter->presentAsProfile($user, $following)
        ]);
    }

    public function followProfile(Request $request, string $username): JsonResponse
    {
        $user = $this->repository->follow(auth()->id(), $username);

        return response()->json([
            'profile' => $this->presenter->presentAsProfile($user, true)
        ]);
    }

    public function unfollowProfile(Request $request, string $username): JsonResponse
    {
        $user = $this->repository->unfollow(auth()->id(), $username);

        return response()->json([
            'profile' => $this->presenter->presentAsProfile($user, false)
        ]);
    }
}

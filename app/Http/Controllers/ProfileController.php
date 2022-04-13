<?php

namespace App\Http\Controllers;

use App\UserJSONPresenter;
use App\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laudis\Neo4j\Types\CypherMap;
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

        return response()->json([
            'profile' => $this->presenter->presentAsProfile($user)
        ]);
    }

    public function followProfile(Request $request, string $username): JsonResponse
    {
        $user = $this->repository->follow(
            auth()->user()->getAuthIdentifier(),
            $username
        );

        return response()->json([
            'profile' => $this->presenter->presentAsProfile($user)
        ]);
    }

    public function unfollowProfile(Request $request, string $username): JsonResponse
    {
        $user = $this->repository->unfollow(
            auth()->user()->getAuthIdentifier(),
            $username
        );

        return response()->json([
            'profile' => $this->presenter->presentAsProfile($user)
        ]);
    }

    /**
     * @param CypherMap $result
     * @return JsonResponse
     */
    private function profileResponseFromArray(CypherMap $result): JsonResponse
    {
        $user = $result->getAsNode('u')->getProperties();
        return response()->json([
            'profile' => [
                'username' => $user['username'],
                'bio' => $user['bio'] ?? '',
                'image' => $user['image'] ?? '',
                'following' => $result->get('self') !== null
            ]
        ]);
    }
}

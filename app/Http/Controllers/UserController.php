<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use App\UserJSONPresenter;
use App\UserRepository;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laudis\Neo4j\Basic\Session;
use function auth;
use function env;
use function response;
use function time;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly UserJSONPresenter $presenter
    )
    {
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->json('user');

        $user = $this->repository->findByEmail($credentials['email']);

        if (!Hash::check($credentials['password'], $user->passwordHash)) {
            return response()->json(['errors' => ['body' => ['Invalid password']]])->setStatusCode(422);
        }

        return response()->json(['user' => $this->presenter->presentAsUser($user)]);
    }

    public function create(Request $request): JsonResponse
    {
        $user = $request->json('user');

        $user = $this->repository->create($user['email'], $user['username'], $user['password']);

        return response()
            ->json(['user' => $this->presenter->presentAsUser($user)])
            ->setStatusCode(201);
    }

    public function update(Request $request): JsonResponse
    {
        $requestedUser = $request->json('user');

        /** @var UserModel|null $authenticatable */
        $authenticatable = auth()->user();
        if ($authenticatable === null ||
            (isset($requestedUser['username']) && $authenticatable->getAuthIdentifier() !== $requestedUser['username']))
        {
            return response()->json()->setStatusCode(401);
        }

        $user = $this->repository->update('', '' , '', '');

        return response()
            ->json(['user' => $this->presenter->presentAsUser($user)]);
    }
}

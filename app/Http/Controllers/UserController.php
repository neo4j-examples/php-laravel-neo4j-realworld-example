<?php

namespace App\Http\Controllers;

use App\Models\User;
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
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->json('user');

        $user = $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $email}) RETURN u
        CYPHER, $credentials)
            ->getAsCypherMap(0)
            ->getAsNode('u')
            ->getProperties();

        if (!Hash::check($credentials['password'], $user['passwordHash'])) {
            return response()->json(['errors' => ['body' => ['Invalid password']]])->setStatusCode(422);
        }

        return $this->userResponseFromArray($user);
    }

    public function create(Request $request): JsonResponse
    {
        $user = $request->json('user');
        $this->session->run(<<<'CYPHER'
        CREATE (user:User {
          email: $email,
          username: $username,
          passwordHash: $passwordHash,
          bio: '',
          image: ''
        })
        CYPHER, [
            'email' => $user['email'],
            'username' => $user['username'],
            'passwordHash' => Hash::make($user['password'])
        ]);

        return $this->userResponseFromArray($user)->setStatusCode(201);
    }

    public function update(Request $request)
    {
        $requestedUser = $request->json('user');

        /** @var User|null $authenticatable */
        $authenticatable = auth()->user();
        if ($authenticatable === null ||
            (isset($requestedUser['email']) && $authenticatable->getAuthIdentifier() !== $requestedUser['email']))
        {
            return response()->json()->setStatusCode(401);
        }

        $user = array_merge((array) $authenticatable->getAttribute('user'), $requestedUser);
        $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $email})
        SET u.username = $username,
            u.bio = $bio,
            u.image = $image
        CYPHER, $user);

        return $this->userResponseFromArray($user);
    }

    public function get()
    {
        $user = auth()->user();
        if ($user === null) {
            return response()->json()->setStatusCode(401);
        }

        return $this->userResponseFromArray((array) $user['user']);
    }

    /**
     * @param $user
     * @return string
     */
    private function createToken($user): string
    {
        $key = env('APP_KEY');
        $payload = array(
            "iss" => env('APP_URL'),
            "aud" => env('APP_URL'),
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + (24 * 60 * 60),
            "user" => [
                'email' => $user['email'],
                'username' => $user['username'],
                'bio' => $user['bio'] ?? '',
                'image' => $user['image'] ?? ''
            ]
        );

        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * @param $user
     * @return JsonResponse
     */
    private function userResponseFromArray($user): JsonResponse
    {
        $jwt = $this->createToken($user);

        return response()->json([
            'user' => [
                'email' => $user['email'],
                'username' => $user['username'],
                'bio' => $user['bio'] ?? '',
                'image' => $user['image'] ?? '',
                'token' => $jwt
            ]
        ]);
    }
}

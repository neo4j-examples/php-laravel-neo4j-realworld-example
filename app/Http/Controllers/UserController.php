<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laudis\Neo4j\Basic\Session;
use function env;
use function response;
use function str_replace;
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

    private function userFromRequest(Request $request): ?array
    {
        $token = $request->header('Authorization', null);

        if ($token === null) {
            return null;
        }

        return (array) JWT::decode(str_replace('Bearer ', '', $token), new Key(env('APP_KEY'), 'HS256'));
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
        $actualUser = $this->userFromRequest($request);
        if ($actualUser !== null) {
            $actualUser = (array)$actualUser['user'];
        }

        if ($actualUser === null ||
            (isset($requestedUser['email']) && $actualUser['email'] !== $requestedUser['email'])
        ) {
            return response()->json()->setStatusCode(401);
        }

        $user = array_merge($actualUser, $requestedUser);
        $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $email})
        SET u.username = $username,
            u.bio = $bio,
            u.image = $image
        CYPHER, $user);

        return $this->userResponseFromArray($user);
    }

    public function get(Request $request)
    {
        $user = $this->userFromRequest($request);
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

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Types\CypherMap;
use function auth;
use function response;

class ProfileController extends Controller
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getProfile(Request $request): JsonResponse
    {
        $parameters = [
            'username' => $request->get('username'),
            'email' => optional(auth()->user())->email
        ];

        $result = $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $username})
        OPTIONAL MATCH (self:User {email: $email}) - [:FOLLOWS] -> (u)
        RETURN self, u
        CYPHER, $parameters)->getAsCypherMap(0);

        return $this->profileResponseFromArray($result);
    }

    public function followProfile(Request $request): JsonResponse
    {
        /** @var User|null $authenticatable */
        $authenticatable = auth()->user();
        if ($authenticatable === null) {
            return response()->json()->setStatusCode(401);
        }

        $parameters = [
            'username' => $request->get('username'),
            'email' => $authenticatable->getAttribute('email')
        ];

        $result = $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $username}), (self:User {email: $email})
        MERGE (self) - [:FOLLOWS] -> (u)
        RETURN self, u
        CYPHER, $parameters)->getAsCypherMap(0);

        return $this->profileResponseFromArray($result);
    }

    public function unfollowProfile(Request $request): JsonResponse
    {
        /** @var User|null $authenticatable */
        $authenticatable = auth()->user();
        if ($authenticatable === null) {
            return response()->json()->setStatusCode(401);
        }

        $parameters = [
            'username' => $request->get('username'),
            'email' => $authenticatable->getAttribute('email')
        ];

        $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $username}) - [f:FOLLOWS] -> (self:User {email: $email})
        DELETE f
        CYPHER, $parameters);

        return $this->getProfile($request);
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

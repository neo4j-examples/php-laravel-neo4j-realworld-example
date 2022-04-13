<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laudis\Neo4j\Basic\Session;
use function auth;
use function response;

class FavoriteController extends Controller
{
    private Session $session;
    private ArticleController $articleController;

    public function __construct(Session $session, ArticleController $articleController)
    {
        $this->session = $session;
        $this->articleController = $articleController;
    }

    public function favorite(Request $request, string $slug): JsonResponse
    {
        /** @var User|null $authenticatable */
        $authenticatable = auth()->user();
        if ($authenticatable === null) {
            return response()->json()->setStatusCode(401);
        }

        $parameters = [
            'slug' => $slug,
            'email' => $authenticatable->getAttribute('user')->email
        ];

        $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $email}), (a:Article {slug: $slug})
        MERGE (u) - [:FAVORITES] -> (a)
        CYPHER, $parameters);

        return $this->articleController->getArticle($request, $slug);
    }

    public function unfavorite(Request $request, string $slug): JsonResponse
    {
        /** @var User|null $authenticatable */
        $authenticatable = auth()->user();
        if ($authenticatable === null) {
            return response()->json()->setStatusCode(401);
        }

        $parameters = [
            'slug' => $slug,
            'email' => $authenticatable->getAttribute('user')->email
        ];

        $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $email}) - [f:FAVORITES] -> (a:Article {slug: $slug})
        DELETE f
        CYPHER, $parameters);

        return $this->articleController->getArticle($request, $slug);
    }
}

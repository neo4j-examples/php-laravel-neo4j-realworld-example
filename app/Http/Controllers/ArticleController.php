<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laudis\Neo4j\Basic\Session;
use function response;

class ArticleController extends Controller
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function listArticles(Request $request): JsonResponse
    {
        return response()->json();
    }

    public function getArticle(Request $request, string $slug): JsonResponse
    {
        return response()->json();
    }

    public function createArticle(Request $request): JsonResponse
    {
        $article = $request->json('article');

        $result = $this->session->run(<<<'CYPHER'
        CREATE (article:Article {title: $title, description: $description, body: $body, createdAt: timestamp()})
        WITH article
        UNWIND $tagList AS tag
        MERGE (t:Tag {name: tag})
        WITH t, article
        MERGE (article) - [:TAGGED] -> (t)
        WITH article, [x IN collect(t) | x.name] AS tags
        RETURN article{.title, .description, .body, tagList: tags}
        CYPHER, $article);
        $article = $result->getAsCypherMap(0)->getAsCypherMap('article');

        return response()->json(['article' => $article], 201);
    }

    public function deleteArticle(Request $request, string $slug): JsonResponse
    {
        return response()->json();
    }

    public function updateArticle(Request $request, string $slug): JsonResponse
    {
        return response()->json();
    }
}

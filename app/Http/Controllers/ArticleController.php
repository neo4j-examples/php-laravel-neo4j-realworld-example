<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laudis\Neo4j\Basic\Session;
use function response;

/**
 * @todo show github link
 * @todo update driver to new beta
 * @todo implement slugging
 * @todo implement article controller
 */
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
        CREATE (article:Article {title: $title, description: $description, body: $body, createdAt: datetime(), updatedAt: datetime()})
        WITH article
        UNWIND $tagList AS tag
        MERGE (t:Tag {name: tag})
        WITH t, article
        MERGE (article) - [:TAGGED] -> (t)
        WITH article, [x IN collect(t) | x.name] AS tags
        RETURN article{.title, .description, .body, tagList: tags, .createdAt, .updatedAt}
        CYPHER, $article);
        $article = $result->getAsCypherMap(0)->getAsCypherMap('article');

        $tbr = $article->toArray();
        $tbr['createdAt'] = $article->getAsDateTime('createdAt')->toDateTime()->format('Y-m-d H:i:s.v') . 'Z';
        $tbr['updatedAt'] = $article->getAsDateTime('updatedAt')->toDateTime()->format('Y-m-d H:i:s.v'). 'Z';

        return response()->json(['article' => $tbr], 201);
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

<?php

namespace App\Http\Controllers;

use Exception;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Map;
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
        $result = $this->session->run(<<<'CYPHER'
        MATCH (a:Article)
        OPTIONAL MATCH (a) - [:TAGGED] -> (t:Tag)
        WITH a, [x IN collect(t) | x.name] AS tags
        RETURN a{.title, .description, .body, tagList: tags, .createdAt, .updatedAt, .slug}
        CYPHER);

        $article = $result->map(function (CypherMap $article) {
            return $this->decorateArticle($article->getAsCypherMap('a'));
        })->toArray();

        return response()->json([
            'articles' => $article,
            'articlesCount' => count($article)
        ]);
    }

    public function getArticle(Request $request, string $slug): JsonResponse
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug})
        OPTIONAL MATCH (a) - [:TAGGED] -> (t:Tag)
        WITH a, [x IN collect(t) | x.name] AS tags
        RETURN a{.title, .description, .body, tagList: tags, .createdAt, .updatedAt, .slug}
        CYPHER, ['slug' => $slug]);

        if ($result->isEmpty()) {
            return response()->json()->setStatusCode(404);
        }
        $article = $result->getAsCypherMap(0)->getAsCypherMap('a');

        return response()->json(['article' => $this->decorateArticle($article)]);
    }

    public function createArticle(Request $request): JsonResponse
    {
        $article = $request->json('article');
        $article['slug'] = Str::slug($article['title']);
        $article['tagList'] ??= [];

        $tsx = $this->session->beginTransaction();

        $tsx->run(<<<'CYPHER'
        CREATE (article:Article {title: $title, description: $description, body: $body, createdAt: datetime(), updatedAt: datetime(), slug: $slug})
        CYPHER, $article);

        $tsx->run(<<<'CYPHER'
        MATCH (article:Article {slug: $slug})
        UNWIND $tagList AS tag
        MERGE (t:Tag {name: tag})
        WITH article, t
        MERGE (article) - [:TAGGED] -> (t)
        CYPHER, $article);

        $tsx->commit();

        return $this->getArticle($request, $article['slug'])->setStatusCode(201);
    }

    public function deleteArticle(Request $request, string $slug): JsonResponse
    {
        $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}) DETACH DELETE a
        CYPHER, ['slug' => $slug]);

        return response()->json();
    }

    public function updateArticle(Request $request, string $slug): JsonResponse
    {
        $parameters = $request->json('article');
        $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug})
        SET a.title = coalesce($parameters['title'], a.title),
            a.description = coalesce($parameters['description'], a.description),
            a.body = coalesce($parameters['body'], a.body)
        CYPHER, ['slug' => $slug, 'parameters' => $parameters]);

        return $this->getArticle($request, $slug)->setStatusCode(200);
    }

    /**
     * @param CypherMap $article
     * @return void
     * @throws Exception
     */
    private function decorateArticle(CypherMap $article): array
    {
        $tbr = $article->toArray();
        $tbr['createdAt'] = $article->getAsDateTime('createdAt')->toDateTime()->format('Y-m-d H:i:s.v') . 'Z';
        $tbr['updatedAt'] = $article->getAsDateTime('updatedAt')->toDateTime()->format('Y-m-d H:i:s.v') . 'Z';

        $tbr['favorited'] = false;
        $tbr['favoritesCount'] = 0;
        $tbr['author'] = [
            'username' => 'bob',
            'bio' => 'programming "cewebrity", missing my girl alice, morning person',
            'image' => '/bob.png',
            'following' => false
        ];

        return $tbr;
    }
}

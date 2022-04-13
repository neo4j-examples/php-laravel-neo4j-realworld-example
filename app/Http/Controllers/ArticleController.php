<?php

namespace App\Http\Controllers;

use App\SlugGenerator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Types\CypherMap;
use function response;

class ArticleController extends Controller
{
    private Session $session;
    private SlugGenerator $slugGenerator;

    public function __construct(Session $session, SlugGenerator $slugGenerator)
    {
        $this->session = $session;
        $this->slugGenerator = $slugGenerator;
    }

    public function listArticles(Request $request): JsonResponse
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (a:Article) <- [:AUTHORED] - (u:User)
        OPTIONAL MATCH (a) - [:TAGGED] -> (t:Tag)
        WITH a, [x IN collect(t) | x.name] AS tags, u
        RETURN a{.title, .description, .body, tagList: tags, .createdAt, .updatedAt, .slug, author: {email: u.email, username: u.username, bio: u.bio, image: u.image, following: false}}
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
        MATCH (a:Article {slug: $slug}) <- [:AUTHORED] - (u:User)
        OPTIONAL MATCH (a) - [:TAGGED] -> (t:Tag)
        WITH a, [x IN collect(t) | x.name] AS tags, u
        RETURN a{.title, .description, .body, tagList: tags, .createdAt, .updatedAt, .slug, author: {email: u.email, username: u.username, bio: u.bio, image: u.image, following: false}}
        CYPHER, ['slug' => $slug]);

        if ($result->isEmpty()) {
            return response()->json()->setStatusCode(404);
        }
        $article = $result->getAsCypherMap(0)->getAsCypherMap('a');

        return response()->json(['article' => $this->decorateArticle($article)]);
    }

    public function createArticle(Request $request): JsonResponse
    {
        $email = optional(auth()->user())->getAuthIdentifier();
        $params = $request->json('article');
        $params['email'] = $email;
        $params['slug'] = $this->slugGenerator->generateSlug('Article', $params['title']);

        $params['tagList'] ??= [];

        $tsx = $this->session->beginTransaction();

        $tsx->run(<<<'CYPHER'
        MATCH (u:User {email: $email})
        CREATE (article:Article {title: $title, description: $description, body: $body, createdAt: datetime(), updatedAt: datetime(), slug: $slug})
        CREATE (u) - [:AUTHORED] -> (article)
        CYPHER, $params);

        $tsx->run(<<<'CYPHER'
        MATCH (article:Article {slug: $slug})
        UNWIND $tagList AS tag
        MERGE (t:Tag {name: tag})
        WITH article, t
        MERGE (article) - [:TAGGED] -> (t)
        CYPHER, $params);

        $tsx->commit();

        return $this->getArticle($request, $params['slug'])->setStatusCode(201);
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

        $favorites = $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug})
        OPTIONAL MATCH (a) <- [:FAVORITES] - (u:User {email: $email})
        WITH a, u
        MATCH (a) <- [:FAVORITES] - (o:User)
        RETURN a, u IS NOT NULL AS favorited, count(o) AS count
        CYPHER, ['slug' => $tbr['slug'], 'email' => optional(auth()->user())->getAuthIdentifier()]);

        if ($favorites->isEmpty()) {
            $tbr['favorited'] = false;
            $tbr['favoritesCount'] = 0;
        } else {
            $tbr['favorited'] = $favorites->first()['favorited'];
            $tbr['favoritesCount'] = $favorites->first()['count'];
        }
        $tbr['author'] = $tbr['author']->toArray();

        return $tbr;
    }
}

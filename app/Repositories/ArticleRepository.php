<?php

namespace App\Repositories;

use App\Models\Article;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Types\Node;
use function abort;

class ArticleRepository
{
    public function __construct(private readonly Session $session)
    {
    }

    public function articlesCount(?string $tag = '', ?string $author = '', ?bool $favorited = null, int $limit = 20, ?int $offset = 0): int
    {
        return $this->session->run(<<<'CYPHER'
        MATCH (article:Article)
        RETURN count(article) AS count
        CYPHER)
            ->getAsCypherMap(0)
            ->getAsInt('count');
    }

    public function listArticles(?string $tag = '', ?string $author = '', ?bool $favorited = null, int $limit = 20, ?int $offset = 0): array
    {
        // todo - list articles
        $result = $this->session->run(<<<'CYPHER'
        MATCH (article:Article)
        RETURN collect(article) AS articles
        CYPHER);

        return $result->getAsCypherMap(0)
            ->getAsArrayList('articles')
            ->map($this->createArticleFromNode(...))
            ->toArray();
    }

    public function findArticle(string $slug): Article
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (article:Article)
        WHERE article.slug = $slug
        RETURN article
        CYPHER, ['slug' => $slug]);

        if ($result->isEmpty()) {
            abort(404, sprintf('Cannot find article with slug: "%s"', $slug));
        }

        return $this->createArticleFromNode($result->getAsCypherMap(0)
            ->getAsNode('article'));
    }

    public function createArticle(string $slug, string $description, string $body, string $title, string $username): void
    {
        $this->session->run(<<<'CYPHER'
        MATCH (u:User {username: $username})
        CREATE (article:Article {title: $title, description: $description, body: $body, createdAt: datetime(), updatedAt: datetime(), slug: $slug})
        CREATE (u) - [:AUTHORED] -> (article)
        CYPHER, ['username' => $username, 'slug' => $slug, 'title' => $title, 'body' => $body, 'description' => $description]);
    }

    public function updateArticle(string $slug, ?string $description, ?string $body, ?string $title): void
    {
        $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug})
        SET a.title = coalesce($title, a.title),
            a.description = coalesce($description, a.description),
            a.body = coalesce($body, a.body)
        CYPHER, ['slug' => $slug, 'title' => $title, 'body' => $body, 'description' => $description]);
    }

    public function deleteArticle(string $slug): void
    {
        $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}) DETACH DELETE a
        CYPHER, ['slug' => $slug]);
    }

    private function createArticleFromNode(Node $node): Article
    {
        $properties = $node->getProperties();
        return new Article(
            slug: $properties->getAsString('slug'),
            title: $properties->getAsString('title'),
            description: $properties->getAsString('description'),
            body: $properties->getAsString('body'),
            createdAt: $properties->getAsDateTime('createdAt')->toDateTime(),
            updatedAt: $properties->get('updatedAt')->toDateTime(),
        );
    }
}

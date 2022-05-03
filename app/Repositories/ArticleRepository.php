<?php

namespace App\Repositories;

use Laudis\Neo4j\Basic\Session;

class ArticleRepository
{
    public function __construct(private readonly Session $session)
    {
    }

    public function listArticles(?string $tag = '', ?string $author = '', ?bool $favorited = null, int $limit = 20, ?int $offset = 0): array
    {
        // todo - list articles
        $result = $this->session->run(<<<'CYPHER'
        MATCH (article:Article) <- [:AUTHORED] - (user:User)
        OPTIONAL MATCH (article) - [:TAGGED] -> (tag:Tag)
        RETURN article, [x IN collect(t) | x.name] AS tags, user
        CYPHER);
    }
}

<?php

namespace App\Repositories;

use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Types\CypherMap;

class FavoriteRepository
{
    public function __construct(private readonly Session $session)
    {
    }

    public function favorite(string $username, string $articleSlug): void
    {
        $this->session->run(<<<'CYPHER'
        MATCH (u:User {username: $username}), (a:Article {slug: $slug})
        MERGE (u) - [:FAVORITES] -> (a)
        CYPHER, [ 'slug' => $articleSlug, 'username' => $username ]);
    }

    public function countFavorites(array $slugs): array
    {
        return $this->session->run(<<<'CYPHER'
        MATCH (a:Article)
        WHERE a.slug IN $slugs
        OPTIONAL MATCH (a) <- [:FAVORITES] - (u:User)
        WITH a.slug AS slug, count(u) AS count
        RETURN apoc.map.fromPairs(collect([slug, count])) AS mapping
        CYPHER, ['slugs' => $slugs])
            ->getAsCypherMap(0)
            ->getAsCypherMap('mapping')
            ->toArray();
    }

    public function favorited(string $username, array $slugs): array
    {
        return $this->session->run(<<<'CYPHER'
        MATCH (a:Article) <- [:FAVORITES] - (u:User {username: $username})
        WHERE a.slug IN $slugs
        RETURN apoc.map.fromPairs(collect([a.slug, true])) AS mapping
        CYPHER, ['slugs' => $slugs, 'username' => $username])
            ->getAsCypherMap(0)
            ->getAsCypherMap('mapping')
            ->toArray();
    }

    public function unfavorite(string $username, string $articleSlug): void
    {
        $this->session->run(<<<'CYPHER'
        MATCH (u:User {username: $username}) - [f:FAVORITES] -> (a:Article {slug: $slug})
        DELETE f
        CYPHER, [ 'slug' => $articleSlug, 'username' => $username ]);
    }
}
